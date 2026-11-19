<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Resources;

use Daika7ana\Innoship\Client;
use Daika7ana\Innoship\DTOs\Locations\LocationFilter;
use Daika7ana\Innoship\DTOs\Locations\LogoRequest;
use Daika7ana\Innoship\DTOs\Locations\LogoResponse;
use Daika7ana\Innoship\DTOs\Locations\UpdateLocation;
use Daika7ana\Innoship\DTOs\Locations\UpdateLocationCourierService;
use Daika7ana\Innoship\Exceptions\AuthenticationException;
use Daika7ana\Innoship\Exceptions\TransportException;
use Daika7ana\Innoship\Exceptions\UnexpectedStatusException;
use Daika7ana\Innoship\Exceptions\ValidationException;
use Daika7ana\Innoship\Support\ApiResponseMapper;
use Daika7ana\SimpleCollections\Collection;

class LocationsResource
{
    public function __construct(
        private Client $client,
    ) {}

    /**
     * List client locations, optionally filtered by country.
     *
     * Returns the decoded JSON list of location summaries.
     *
     * @return Collection<array-key, mixed>
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function list(?string $countryCode = null): Collection
    {
        $response = $this->client->request('GET', '/api/ClientLocation', null, [
            'countryCode' => $countryCode,
        ]);

        return new Collection(ApiResponseMapper::decodeOptionalJson($response, 200));
    }

    /**
     * Get a single client location.
     *
     * @return array<string, mixed>
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function get(int $id): array
    {
        $response = $this->client->request('GET', sprintf('/api/ClientLocation/%d', $id));

        return ApiResponseMapper::decodeOptionalJson($response, 200);
    }

    /**
     * Create a client location.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function create(UpdateLocation $location): int
    {
        $response = $this->client->request('POST', '/api/ClientLocation', $location);
        $value = ApiResponseMapper::decodeJsonValue($response, 200);

        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && ctype_digit($value)) {
            return (int) $value;
        }

        if (is_array($value) && isset($value['id']) && is_numeric($value['id'])) {
            return (int) $value['id'];
        }

        throw new UnexpectedStatusException($response, 'Expected a client location id.');
    }

    /**
     * Update a client location.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function update(int $id, UpdateLocation $location): bool
    {
        $response = $this->client->request('PUT', sprintf('/api/ClientLocation/%d', $id), $location);

        return (bool) ApiResponseMapper::decodeJsonValue($response, 200);
    }

    /**
     * Delete a client location.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function delete(int $id): bool
    {
        $response = $this->client->request('DELETE', sprintf('/api/ClientLocation/%d', $id));

        return (bool) ApiResponseMapper::decodeJsonValue($response, 200);
    }

    /**
     * Summarize client locations matching a filter.
     *
     * @return array<string, mixed>
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function summary(LocationFilter $filter): array
    {
        $response = $this->client->request('POST', '/api/ClientLocation/Summary', $filter);

        return ApiResponseMapper::decodeOptionalJson($response, 200);
    }

    /**
     * Validate a client location payload without saving it.
     *
     * @return array<string, mixed>
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function validate(UpdateLocation $location): array
    {
        $response = $this->client->request('POST', '/api/ClientLocation/Validate', $location);

        return ApiResponseMapper::decodeOptionalJson($response, 200);
    }

    /**
     * Attach a courier to a client location.
     *
     * @return array<string, mixed>
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function addCourier(int $locationId, int $courierId, bool $isEmagMarketPlace = false): array
    {
        $response = $this->client->request('POST', sprintf('/api/ClientLocation/%d/Courier', $locationId), [
            'courierId' => $courierId,
            'isEmagMarketPlace' => $isEmagMarketPlace,
        ]);

        return ApiResponseMapper::decodeOptionalJson($response, 200);
    }

    /**
     * Detach a courier from a client location.
     *
     * @return array<string, mixed>
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function removeCourier(
        int $locationId,
        int $courierId,
        bool $force = false,
        bool $isEmagMarketPlace = false,
    ): array {
        $response = $this->client->request(
            'DELETE',
            sprintf('/api/ClientLocation/%d/Courier/%d', $locationId, $courierId),
            null,
            [
                'force' => $force,
                'isEmagMarketPlace' => $isEmagMarketPlace,
            ],
        );

        return ApiResponseMapper::decodeOptionalJson($response, 200);
    }

    /**
     * Update the courier services of a client location.
     *
     * @param list<UpdateLocationCourierService> $services
     *
     * @return array<string, mixed>
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function updateCourierServices(
        int $locationId,
        array $services,
        bool $applyToAllLocations = false,
        bool $patch = false,
    ): array {
        $body = array_map(
            static fn(UpdateLocationCourierService $service): array => $service->toArray(),
            $services,
        );

        $response = $this->client->request(
            $patch ? 'PATCH' : 'PUT',
            sprintf('/api/ClientLocation/%d/CourierServices', $locationId),
            $body,
            ['applyToAllLocations' => $applyToAllLocations],
        );

        return ApiResponseMapper::decodeOptionalJson($response, 200);
    }

    /**
     * Upload a client location logo, replacing the existing one when requested.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function uploadLogo(int $locationId, string $base64Logo, bool $replace = false): LogoResponse
    {
        $response = $this->client->request(
            $replace ? 'PUT' : 'POST',
            sprintf('/api/ClientLocation/%d/Logo', $locationId),
            new LogoRequest(base64Logo: $base64Logo),
        );

        return LogoResponse::fromArray(ApiResponseMapper::decodeJson($response, 200));
    }

    /**
     * Delete a client location logo.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function deleteLogo(int $locationId): LogoResponse
    {
        $response = $this->client->request('DELETE', sprintf('/api/ClientLocation/%d/Logo', $locationId));

        return LogoResponse::fromArray(ApiResponseMapper::decodeJson($response, 200));
    }
}
