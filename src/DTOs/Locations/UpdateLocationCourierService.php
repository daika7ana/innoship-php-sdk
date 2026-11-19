<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Locations;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Support\SerializesToArray;

/**
 * Courier service configuration of a client location.
 */
final readonly class UpdateLocationCourierService implements Arrayable
{
    use SerializesToArray;

    /**
     * @param array<string, mixed>|null $courierServiceRestrictions
     * @param array<string, mixed>|null $extraServices
     * @param array<string, mixed>|null $mappingReferences
     * @param array<string, mixed>|null $countryDeliveryDays
     * @param array<string, mixed>|null $defaultLockerValues
     */
    public function __construct(
        public int $courierId = 0,
        public int $serviceId = 0,
        public ?array $courierServiceRestrictions = null,
        public ?bool $useCourierPrices = null,
        public ?string $mappingTemplateOptionId = null,
        public ?string $voucherTemplateOptionId = null,
        public ?array $extraServices = null,
        public ?array $mappingReferences = null,
        public ?array $countryDeliveryDays = null,
        public ?bool $enabled = null,
        public ?int $localityKm = null,
        public ?array $defaultLockerValues = null,
        public ?int $priceLocalityId = null,
    ) {}
}
