<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Couriers;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Support\SerializesToArray;

/**
 * Address used when generating a courier location id.
 */
final readonly class CourierLocationAddress implements Arrayable
{
    use SerializesToArray;

    public function __construct(
        public ?string $name = null,
        public ?string $contactPerson = null,
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $localityName = null,
        public ?string $addressText = null,
        public ?string $streetType = null,
        public ?string $streetName = null,
        public ?string $streetNumber = null,
        public ?string $postalCode = null,
        public ?string $countyName = null,
        public ?string $countryCode = null,
        public ?string $countryName = null,
    ) {}
}
