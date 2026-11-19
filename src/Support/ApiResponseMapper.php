<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Support;

use Daika7ana\Innoship\DTOs\Common\ErrorResponseItem;
use Daika7ana\Innoship\Exceptions\AuthenticationException;
use Daika7ana\Innoship\Exceptions\UnexpectedStatusException;
use Daika7ana\Innoship\Exceptions\ValidationException;
use Daika7ana\SimpleCollections\Collection;
use JsonException;
use Psr\Http\Message\ResponseInterface;

final class ApiResponseMapper
{
    /**
     * @param int|list<int> $expectedStatus
     *
     * @throws AuthenticationException
     * @throws UnexpectedStatusException
     * @throws ValidationException
     * @return array<string, mixed>
     */
    public static function decodeJson(ResponseInterface $response, int|array $expectedStatus = 200): array
    {
        self::assertStatus($response, $expectedStatus);

        try {
            return JsonHelper::decode((string) $response->getBody());
        } catch (JsonException $e) {
            throw new UnexpectedStatusException(
                $response,
                'Response body is not valid JSON: ' . $e->getMessage(),
            );
        }
    }

    /**
     * Decodes a JSON body, treating an empty body as an empty array.
     *
     * @param int|list<int> $expectedStatus
     *
     * @throws AuthenticationException
     * @throws UnexpectedStatusException
     * @throws ValidationException
     * @return array<string, mixed>
     */
    public static function decodeOptionalJson(ResponseInterface $response, int|array $expectedStatus = 200): array
    {
        self::assertStatus($response, $expectedStatus);

        $contents = (string) $response->getBody();

        if (trim($contents) === '') {
            return [];
        }

        try {
            return JsonHelper::decode($contents);
        } catch (JsonException $e) {
            throw new UnexpectedStatusException(
                $response,
                'Response body is not valid JSON: ' . $e->getMessage(),
            );
        }
    }

    /**
     * Decodes any JSON value, including scalars such as `true`.
     *
     * @param int|list<int> $expectedStatus
     *
     * @throws AuthenticationException
     * @throws UnexpectedStatusException
     * @throws ValidationException
     */
    public static function decodeJsonValue(ResponseInterface $response, int|array $expectedStatus = 200): mixed
    {
        self::assertStatus($response, $expectedStatus);

        try {
            return json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new UnexpectedStatusException(
                $response,
                'Response body is not valid JSON: ' . $e->getMessage(),
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public static function arrayOrEmpty(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function listOfArrays(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $items = [];

        foreach ($value as $item) {
            if (is_array($item)) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @return list<string>
     */
    public static function listOfStrings(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $items = [];

        foreach ($value as $item) {
            if (is_scalar($item)) {
                $items[] = (string) $item;
            }
        }

        return $items;
    }

    /**
     * @param int|list<int> $expectedStatus
     *
     * @throws AuthenticationException
     * @throws UnexpectedStatusException
     * @throws ValidationException
     */
    public static function assertStatus(ResponseInterface $response, int|array $expectedStatus = 200): void
    {
        $statusCode = $response->getStatusCode();

        if (in_array($statusCode, (array) $expectedStatus, true)) {
            return;
        }

        [$message, $errors, $correlationId] = self::decodeErrorPayload((string) $response->getBody());

        if ($statusCode === 401) {
            throw new AuthenticationException($message !== '' ? $message : 'Unauthorized');
        }

        if ($statusCode === 400 || $statusCode === 422) {
            throw new ValidationException($errors, $message, $correlationId);
        }

        throw new UnexpectedStatusException($response, $message);
    }

    /**
     * @return array{string, list<ErrorResponseItem>, ?string}
     */
    private static function decodeErrorPayload(string $payload): array
    {
        try {
            $decoded = JsonHelper::decode($payload);
        } catch (\Throwable) {
            return [$payload !== '' ? $payload : 'Request failed', [], null];
        }

        $rawErrors = $decoded['errors'] ?? null;
        $errors = [];

        if (is_array($rawErrors)) {
            // Innoship returns a flat list; ASP.NET model binding returns field => messages.
            foreach ($rawErrors as $key => $item) {
                if (is_array($item) && array_is_list($rawErrors)) {
                    $errors[] = ErrorResponseItem::fromArray($item);
                    continue;
                }

                if (is_array($item)) {
                    $details = array_filter($item, static fn(mixed $detail): bool => is_scalar($detail));
                    $errors[] = new ErrorResponseItem(
                        message: is_string($key) ? $key : '',
                        details: new Collection(array_map(static fn(mixed $detail): string => (string) $detail, array_values($details))),
                    );
                }
            }
        }

        $correlationId = isset($decoded['correlationId']) && is_string($decoded['correlationId'])
            ? $decoded['correlationId']
            : null;

        $message = $errors[0]->message ?? '';

        if ($message === '' && isset($decoded['message']) && is_string($decoded['message'])) {
            $message = $decoded['message'];
        }

        if ($message === '') {
            $message = 'Request failed';
        }

        $details = $errors[0]->details ?? new Collection();

        if ($details->isNotEmpty()) {
            $message .= ': ' . implode('; ', $details->all());
        }

        return [$message, $errors, $correlationId];
    }
}
