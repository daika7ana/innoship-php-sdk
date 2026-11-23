<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Tests\Unit\Support;

use Daika7ana\Innoship\Exceptions\AuthenticationException;
use Daika7ana\Innoship\Exceptions\UnexpectedStatusException;
use Daika7ana\Innoship\Exceptions\ValidationException;
use Daika7ana\Innoship\Support\ApiResponseMapper;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ApiResponseMapperTest extends TestCase
{
    public function testDecodeJsonReturnsPayload(): void
    {
        $response = new Response(200, [], '{"courier":1,"success":true}');

        $this->assertSame(['courier' => 1, 'success' => true], ApiResponseMapper::decodeJson($response));
    }

    public function testDecodeOptionalJsonReturnsEmptyArrayForEmptyBody(): void
    {
        $response = new Response(200, [], '');

        $this->assertSame([], ApiResponseMapper::decodeOptionalJson($response));
    }

    public function testValidationErrorThrowsWithParsedErrors(): void
    {
        $response = new Response(400, [], json_encode([
            'errors' => [
                [
                    'message' => 'Invalid order data',
                    'details' => ['addressTo is required'],
                    'categoryCode' => 'Validation',
                    'subcategoryCode' => 'Order',
                ],
            ],
            'correlationId' => 'corr-123',
        ], JSON_THROW_ON_ERROR));

        try {
            ApiResponseMapper::decodeJson($response);
            $this->fail('Expected ValidationException.');
        } catch (ValidationException $e) {
            $this->assertSame('Invalid order data: addressTo is required', $e->getMessage());
            $this->assertSame('corr-123', $e->correlationId);
            $this->assertCount(1, $e->errors);
            $this->assertSame('Invalid order data', $e->errors[0]->message);
            $this->assertSame(['addressTo is required'], $e->errors[0]->details->all());
            $this->assertSame('Order', $e->errors[0]->subcategoryCode);
        }
    }

    public function testValidationExceptionExposesTheResponse(): void
    {
        $response = new Response(422, [], '{"errors":[{"message":"Invalid order data"}]}');

        try {
            ApiResponseMapper::decodeJson($response);
            $this->fail('Expected ValidationException.');
        } catch (ValidationException $e) {
            $this->assertSame($response, $e->response);
            $this->assertSame(422, $e->response?->getStatusCode());
            $this->assertSame(422, $e->getCode());
        }
    }

    public function testAspnetFieldErrorsAreFlattened(): void
    {
        $response = new Response(400, [], json_encode([
            'errors' => [
                'addressTo.name' => ['The Name field is required.'],
            ],
        ], JSON_THROW_ON_ERROR));

        try {
            ApiResponseMapper::decodeJson($response);
            $this->fail('Expected ValidationException.');
        } catch (ValidationException $e) {
            $this->assertCount(1, $e->errors);
            $this->assertSame('addressTo.name', $e->errors[0]->message);
            $this->assertSame(['The Name field is required.'], $e->errors[0]->details->all());
        }
    }

    public function testUnauthorizedThrowsAuthenticationException(): void
    {
        $response = new Response(401, [], '{"errors":[{"message":"Invalid API key"}]}');

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid API key');

        ApiResponseMapper::decodeJson($response);
    }

    public function testUnexpectedStatusThrows(): void
    {
        $response = new Response(500, [], '{"errors":[{"message":"Server exploded"}]}');

        try {
            ApiResponseMapper::decodeJson($response);
            $this->fail('Expected UnexpectedStatusException.');
        } catch (UnexpectedStatusException $e) {
            $this->assertSame(500, $e->response->getStatusCode());
            $this->assertSame(500, $e->getCode());
            $this->assertSame('Server exploded', $e->getMessage());
        }
    }

    public function testAcceptListOfExpectedStatuses(): void
    {
        $response = new Response(201, [], '{"success":true}');

        $this->assertSame(['success' => true], ApiResponseMapper::decodeJson($response, [200, 201]));
    }

    public function testInvalidJsonThrowsUnexpectedStatus(): void
    {
        $this->expectException(UnexpectedStatusException::class);

        ApiResponseMapper::decodeJson(new Response(200, [], 'not json'));
    }

    public function testValidationExceptionCarriesParsedError(): void
    {
        $response = new Response(422, [], json_encode([
            'errors' => [
                ['message' => 'Invalid order data', 'categoryCode' => 'Validation', 'subcategoryCode' => 'Order'],
            ],
            'correlationId' => 'corr-123',
        ], JSON_THROW_ON_ERROR));

        try {
            ApiResponseMapper::decodeJson($response);
            $this->fail('Expected ValidationException.');
        } catch (ValidationException $e) {
            $this->assertNotNull($e->error);
            $this->assertSame(422, $e->error->code);
            $this->assertSame('Invalid order data', $e->error->message);
            $this->assertSame('corr-123', $e->error->id);
            $this->assertSame('Validation', $e->error->context);
            $this->assertSame('Order', $e->error->component);
            $this->assertTrue($e->error->isValidationRelated());
        }
    }

    public function testAuthenticationExceptionCarriesParsedError(): void
    {
        $response = new Response(401, [], '{"errors":[{"message":"Invalid API key"}]}');

        try {
            ApiResponseMapper::decodeJson($response);
            $this->fail('Expected AuthenticationException.');
        } catch (AuthenticationException $e) {
            $this->assertNotNull($e->error);
            $this->assertSame(401, $e->getCode());
            $this->assertSame(401, $e->error->code);
            $this->assertSame('Invalid API key', $e->error->message);
            $this->assertTrue($e->error->isAuthenticationRelated());
        }
    }

    public function testUnexpectedStatusCarriesParsedError(): void
    {
        $response = new Response(500, [], '{"errors":[{"message":"Server exploded"}],"correlationId":"corr-500"}');

        try {
            ApiResponseMapper::decodeJson($response);
            $this->fail('Expected UnexpectedStatusException.');
        } catch (UnexpectedStatusException $e) {
            $this->assertNotNull($e->error);
            $this->assertSame(500, $e->error->code);
            $this->assertSame('Server exploded', $e->error->message);
            $this->assertSame('corr-500', $e->error->id);
        }
    }

    public function testUnexpectedStatusExposesRetryAfterHeader(): void
    {
        try {
            ApiResponseMapper::decodeJson(new Response(503, ['Retry-After' => '30'], 'down'));
            $this->fail('Expected UnexpectedStatusException.');
        } catch (UnexpectedStatusException $exception) {
            $this->assertSame(30, $exception->retryAfter);
        }

        try {
            ApiResponseMapper::decodeJson(new Response(503, [], 'down'));
            $this->fail('Expected UnexpectedStatusException.');
        } catch (UnexpectedStatusException $exception) {
            $this->assertNull($exception->retryAfter);
        }
    }
}
