<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Resources;

use Daika7ana\Innoship\Client;
use Daika7ana\Innoship\DTOs\Couriers\CourierLocationsResponse;
use Daika7ana\Innoship\DTOs\Couriers\GenerateCourierLocationIdRequest;
use Daika7ana\Innoship\DTOs\Couriers\GenerateCourierLocationIdResponse;
use Daika7ana\Innoship\DTOs\Couriers\PickupRequest;
use Daika7ana\Innoship\DTOs\Couriers\PickupResponse;
use Daika7ana\Innoship\DTOs\Couriers\SetCourierLocationResponse;
use Daika7ana\Innoship\Exceptions\AuthenticationException;
use Daika7ana\Innoship\Exceptions\TransportException;
use Daika7ana\Innoship\Exceptions\UnexpectedStatusException;
use Daika7ana\Innoship\Exceptions\ValidationException;
use Daika7ana\Innoship\Support\ApiResponseMapper;

class CouriersResource
{
    public function __construct(
        private Client $client,
    ) {}

    /**
     * Schedule a courier pickup.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function requestPickup(PickupRequest $request): PickupResponse
    {
        $response = $this->client->request('POST', '/api/Courier/RequestPickup', $request);

        return PickupResponse::fromArray(ApiResponseMapper::decodeJson($response, 200));
    }

    /**
     * Generate a courier location id for a client location.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function generateLocationId(
        int $locationId,
        int $courierId,
        ?GenerateCourierLocationIdRequest $request = null,
    ): GenerateCourierLocationIdResponse {
        $response = $this->client->request(
            'POST',
            sprintf(
                '/api/Courier/Location/GenerateId/%d/%d',
                $locationId,
                $courierId,
            ),
            $request,
        );

        return GenerateCourierLocationIdResponse::fromArray(ApiResponseMapper::decodeJson($response, 200));
    }

    /**
     * List the courier locations configured for a client location.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function listLocations(int $locationId, int $courierId): CourierLocationsResponse
    {
        $response = $this->client->request(
            'GET',
            sprintf('/api/Courier/Location/List/%d/%d', $locationId, $courierId),
        );

        return CourierLocationsResponse::fromArray(ApiResponseMapper::decodeJson($response, 200));
    }

    /**
     * Assign a courier location id to a client location.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function setLocationId(int $locationId, int $courierId, int $courierLocationId): SetCourierLocationResponse
    {
        $response = $this->client->request(
            'POST',
            sprintf(
                '/api/Courier/Location/SetId/%d/%d/%d',
                $locationId,
                $courierId,
                $courierLocationId,
            ),
        );

        return SetCourierLocationResponse::fromArray(ApiResponseMapper::decodeJson($response, 200));
    }
}
