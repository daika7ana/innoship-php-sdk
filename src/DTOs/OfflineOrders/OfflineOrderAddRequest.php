<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\OfflineOrders;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Enums\Courier;
use Daika7ana\Innoship\Support\SerializesToArray;

/**
 * Offline order submitted through the add endpoint.
 */
final readonly class OfflineOrderAddRequest implements Arrayable
{
    use SerializesToArray;

    /**
     * @param list<OfflineOrderParcel>|null $parcels
     */
    public function __construct(
        public ?Courier $courier = null,
        public ?string $courierShipmentId = null,
        public bool $finalParcel = false,
        public ?array $parcels = null,
    ) {}
}
