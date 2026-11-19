<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Locations;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Support\SerializesToArray;

/**
 * Address of a client location.
 */
final readonly class LocationAddress implements Arrayable
{
    use SerializesToArray;

    public function __construct(
        public ?string $name = null,
        public ?string $contactPerson = null,
        public ?string $phone = null,
        public ?string $email = null,
        public ?int $localityId = null,
        public ?string $localityName = null,
        public ?string $addressText = null,
        public ?string $streetType = null,
        public ?string $streetName = null,
        public ?string $streetNumber = null,
        public ?string $postalCode = null,
        public ?int $countyId = null,
        public ?string $countyName = null,
        public ?string $countryCode = null,
        public ?string $countryName = null,
        public bool $ignoreLocalityId = false,
        public ?float $latitude = null,
        public ?float $longitude = null,
    ) {}
}
