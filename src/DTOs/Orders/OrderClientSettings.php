<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Orders;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Support\SerializesToArray;

final readonly class OrderClientSettings implements Arrayable
{
    use SerializesToArray;

    /**
     * @param array<string, float>|null $performanceClientRates
     */
    public function __construct(
        public ?string $trackPageDomainName = null,
        public ?float $priceWeight = null,
        public ?float $performanceWeight = null,
        public ?float $performanceMaximumPriceDeviation = null,
        public ?float $performanceMaximumFlatPriceDeviation = null,
        public ?float $performancePreferredMinimumPriceThreshold = null,
        public ?ClientPreferences $clientPreferences = null,
        public ?string $senderLocationTimeZoneId = null,
        public ?bool $doNoSaveGDPRData = null,
        public ?bool $openPackageIsOptional = null,
        public ?bool $saturdayDeliveryIsOptional = null,
        public ?int $uniqueOrderReferenceId = null,
        public ?bool $useInnoshipInsurance = null,
        public ?array $performanceClientRates = null,
    ) {}
}
