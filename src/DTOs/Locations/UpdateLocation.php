<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Locations;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Support\SerializesToArray;

/**
 * Create/update payload for a client location.
 */
final readonly class UpdateLocation implements Arrayable
{
    use SerializesToArray;

    /**
     * @param array<string, mixed>|null $courierVoucherService
     * @param array<string, mixed>|null $marketPlaceCallBackSettings
     */
    public function __construct(
        public ?LocationAddress $address = null,
        public ?string $courierCutOffTimes = null,
        public ?string $courierAutomaticCallTimes = null,
        public ?bool $useSameCourierForSameAddress = null,
        public ?int $courierFailPolicy = null,
        public ?int $streetNumberExtractPolicy = null,
        public ?string $streetNumberExtract = null,
        public ?string $timeZoneId = null,
        public ?int $priceGroupId = null,
        public ?string $dynamicReferencesGroupId = null,
        public ?string $notificationEmail = null,
        public ?string $notificationPhone = null,
        public ?array $courierVoucherService = null,
        public ?array $marketPlaceCallBackSettings = null,
        public ?ReturnPortalSettings $returnPortalSettings = null,
        public ?bool $importIsActive = null,
        public ?bool $setHandoverOnManifest = null,
        public ?bool $ignorePricesWhenMissing = null,
        public ?bool $disableTrack = null,
        public ?int $overwriteTrackingLocationId = null,
        public ?int $id = null,
        public ?string $name = null,
        public ?string $externalLocationId = null,
    ) {}
}
