<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\OfflineOrders;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Enums\Courier;
use Daika7ana\Innoship\Support\SerializesToArray;

/**
 * Offline order submitted through the activate endpoint.
 */
final readonly class ActivateOrderRequest implements Arrayable
{
    use SerializesToArray;

    /**
     * @param list<ActivateOrderParcel>|null $parcels
     */
    public function __construct(
        public ?Courier $courier = null,
        public ?string $courierShipmentId = null,
        public ?string $shipmentDate = null,
        public ?string $shipmentDateEnd = null,
        public ?array $parcels = null,
        public ?ActivateAddress $addressFrom = null,
        public ?ActivateAddress $addressTo = null,
        public ?ActivateParameters $parameters = null,
    ) {}
}
