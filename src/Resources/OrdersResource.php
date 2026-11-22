<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Resources;

use Daika7ana\Innoship\Client;
use Daika7ana\Innoship\DTOs\Orders\CourierDeleteParcelsResponse;
use Daika7ana\Innoship\DTOs\Orders\DeleteOrderResponse;
use Daika7ana\Innoship\DTOs\Orders\DocumentResponse;
use Daika7ana\Innoship\DTOs\Orders\OrderRequest;
use Daika7ana\Innoship\DTOs\Orders\OrderResponse;
use Daika7ana\Innoship\DTOs\Orders\OrderSimulationResult;
use Daika7ana\Innoship\DTOs\Orders\UpdateParcelsRequest;
use Daika7ana\Innoship\DTOs\Orders\UpdateStatusRequest;
use Daika7ana\Innoship\DTOs\Orders\UploadDocumentsRequest;
use Daika7ana\Innoship\DTOs\Orders\UploadDocumentsResponse;
use Daika7ana\Innoship\Enums\DocumentType;
use Daika7ana\Innoship\Enums\FileFormat;
use Daika7ana\Innoship\Enums\FileType;
use Daika7ana\Innoship\Exceptions\AuthenticationException;
use Daika7ana\Innoship\Exceptions\TransportException;
use Daika7ana\Innoship\Exceptions\UnexpectedStatusException;
use Daika7ana\Innoship\Exceptions\ValidationException;
use Daika7ana\Innoship\Support\ApiResponseMapper;
use Daika7ana\SimpleCollections\Collection;

class OrdersResource
{
    public function __construct(
        private Client $client,
    ) {}

    /**
     * Creates a new order.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function create(OrderRequest $order): OrderResponse
    {
        $response = $this->client->request('POST', '/api/Order', $order);

        return OrderResponse::fromArray(ApiResponseMapper::decodeJson($response, [201, 200]));
    }

    /**
     * Simulates creating an order without persisting it.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function simulate(OrderRequest $order): OrderResponse
    {
        $response = $this->client->request('POST', '/api/Order/simulate', $order);

        return OrderResponse::fromArray(ApiResponseMapper::decodeJson($response, [201, 200]));
    }

    /**
     * Validates an order request.
     *
     * The API answers with a JSON boolean rather than an order payload.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function validate(OrderRequest $order): bool
    {
        $response = $this->client->request('POST', '/api/Order/validate', $order);

        $value = ApiResponseMapper::decodeJsonValue($response, [200, 201]);

        if (!is_bool($value)) {
            throw new UnexpectedStatusException(
                $response,
                'Expected a boolean validation result.',
                retryAfter: ApiResponseMapper::retryAfter($response),
            );
        }

        return $value;
    }

    /**
     * Simulates multiple orders and maps results by request key.
     *
     * @param list<OrderRequest> $orders
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     * @return Collection<string, OrderSimulationResult>
     */
    public function simulateMultiple(array $orders): Collection
    {
        $body = array_map(static fn(OrderRequest $order): array => $order->toArray(), $orders);

        $response = $this->client->request('POST', '/api/Order/multiple/simulate', $body);

        $results = [];

        foreach (ApiResponseMapper::decodeJson($response, [201, 200]) as $key => $value) {
            if (is_array($value)) {
                $results[(string) $key] = OrderSimulationResult::fromArray($value);
            }
        }

        return new Collection($results);
    }

    /**
     * Imports orders from a CSV file.
     *
     * The published Swagger omits this endpoint's body schema; the multipart
     * field name follows the API convention.
     *
     * @param list<int> $userAllowedLocationIds
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     * @return array<string, mixed>
     */
    public function importFile(string $fileContents, string $filename = 'orders.csv', array $userAllowedLocationIds = []): array
    {
        $boundary = bin2hex(random_bytes(16));
        $body = sprintf(
            "--%1\$s\r\nContent-Disposition: form-data; name=\"file\"; filename=\"%2\$s\"\r\nContent-Type: text/csv\r\n\r\n%3\$s\r\n--%1\$s--\r\n",
            $boundary,
            str_replace(["\r", "\n", '"', '\\'], '', $filename),
            $fileContents,
        );

        $path = '/api/Order/file';
        $query = [];

        foreach ($userAllowedLocationIds as $locationId) {
            $query[] = 'userAllowedLocationIds=' . rawurlencode((string) $locationId);
        }

        if ($query !== []) {
            $path .= '?' . implode('&', $query);
        }

        $request = $this->client->createRequest('POST', $path)
            ->withHeader('Content-Type', 'multipart/form-data; boundary=' . $boundary)
            ->withBody($this->client->streamFactory()->createStream($body));

        $response = $this->client->send($request);

        return ApiResponseMapper::decodeJson($response, 201);
    }

    /**
     * Deletes an existing order by courier and AWB.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function delete(int $courierId, string $awb): DeleteOrderResponse
    {
        $path = sprintf('/api/Order/%d/awb/%s', $courierId, rawurlencode($awb));
        $response = $this->client->request('DELETE', $path);

        return DeleteOrderResponse::fromArray(ApiResponseMapper::decodeJson($response, [201, 200]));
    }

    /**
     * Deletes a single parcel from an order.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function deleteParcel(int $courierId, string $awb, string $parcelBarcode): CourierDeleteParcelsResponse
    {
        $path = sprintf(
            '/api/Order/%d/awb/%s/parcel/%s',
            $courierId,
            rawurlencode($awb),
            rawurlencode($parcelBarcode),
        );
        $response = $this->client->request('DELETE', $path);

        return CourierDeleteParcelsResponse::fromArray(ApiResponseMapper::decodeJson($response, [201, 200]));
    }

    /**
     * Updates the statuses of multiple orders.
     *
     * @param list<UpdateStatusRequest> $requests
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     * @return array<string, mixed>
     */
    public function updateStatus(array $requests): array
    {
        $body = array_map(static fn(UpdateStatusRequest $request): array => $request->toArray(), $requests);

        $response = $this->client->request('POST', '/api/Order/UpdateStatus', $body);

        return ApiResponseMapper::decodeOptionalJson($response, 200);
    }

    /**
     * Recreates an order with updated parcels and cancels the original.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function updateParcels(UpdateParcelsRequest $request): OrderResponse
    {
        $response = $this->client->request('POST', '/api/Order/UpdateParcels', $request);

        return OrderResponse::fromArray(ApiResponseMapper::decodeJson($response, [201, 200]));
    }

    /**
     * Updates the custom attributes of an order.
     *
     * @param array<string, mixed> $attributes
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     * @return array<string, mixed>
     */
    public function updateCustomAttributes(int $courierId, string $awb, array $attributes): array
    {
        $path = sprintf('/api/Order/UpdateCustomAttributes/%d/awb/%s', $courierId, rawurlencode($awb));
        $response = $this->client->request('POST', $path, $attributes);

        return ApiResponseMapper::decodeOptionalJson($response, 200);
    }

    /**
     * Gets return AWB information.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     * @return array<string, mixed>
     */
    public function getReturnAwbInfo(int $courierId, string $returnAwb): array
    {
        $path = sprintf('/api/Order/ReturnAwbInfo/%d/%s', $courierId, rawurlencode($returnAwb));
        $response = $this->client->request('GET', $path);

        return ApiResponseMapper::decodeOptionalJson($response, 200);
    }

    /**
     * Uploads documents for an order.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function uploadDocuments(UploadDocumentsRequest $request): UploadDocumentsResponse
    {
        $response = $this->client->request('POST', '/api/Order/UploadDocuments', $request);

        return UploadDocumentsResponse::fromArray(ApiResponseMapper::decodeJson($response, 200));
    }

    /**
     * Downloads a document for an order.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function downloadDocument(
        int $courierId,
        string $awb,
        ?DocumentType $document = null,
        ?FileType $type = null,
        ?FileFormat $format = null,
        bool $useFile = true,
    ): DocumentResponse {
        $path = sprintf('/api/Order/DownloadDocument/courier/%d/awb/%s', $courierId, rawurlencode($awb));
        $query = array_filter([
            'document' => $document?->value,
            'type' => $type?->value,
            'format' => $format?->value,
            'useFile' => $useFile,
        ], static fn(mixed $value): bool => $value !== null);

        $response = $this->client->request('GET', $path, null, $query);

        return DocumentResponse::fromArray(ApiResponseMapper::decodeJson($response, 200));
    }
}
