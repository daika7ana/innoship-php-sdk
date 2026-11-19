<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Orders;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Support\SerializesToArray;

/**
 * Sender, recipient or return address of an order request.
 *
 * Reference this class for every request DTO: readonly properties in API
 * camelCase, all optional with defaults, serialized through SerializesToArray.
 */
final readonly class OrderRequestAddress implements Arrayable
{
    use SerializesToArray;

    public function __construct(
        public ?string $name = null,
        public ?string $contactPerson = null,
        public ?string $country = null,
        public ?string $countryName = null,
        public ?int $countyId = null,
        public ?int $localityId = null,
        public ?int $countryId = null,
        public ?string $countyName = null,
        public ?string $localityName = null,
        public ?string $addressText = null,
        public ?string $streetType = null,
        public ?string $streetName = null,
        public ?string $streetNumber = null,
        public ?string $postalCode = null,
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $fixedLocationId = null,
        public ?string $fixedLocationName = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public ?string $courierFixedLocationId = null,
    ) {}
}
