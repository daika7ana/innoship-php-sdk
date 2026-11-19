<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\OfflineOrders;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Support\SerializesToArray;

/**
 * Sender or recipient address of an activate request.
 */
final readonly class ActivateAddress implements Arrayable
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
        public ?string $streetNumber = null,
        public ?string $streetName = null,
        public ?string $streetType = null,
        public ?string $postalCode = null,
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $courierFixedLocationId = null,
        public ?string $courierFixedLocationName = null,
    ) {}
}
