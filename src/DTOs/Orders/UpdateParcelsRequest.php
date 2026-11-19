<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Orders;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Enums\Courier;
use Daika7ana\Innoship\Support\SerializesToArray;

final readonly class UpdateParcelsRequest implements Arrayable
{
    use SerializesToArray;

    /**
     * @param list<OrderParcel>|null $parcels
     */
    public function __construct(
        public ?Courier $courierId = null,
        public ?string $courierShipmentId = null,
        public ?int $envelopeCount = null,
        public ?int $parcelsCount = null,
        public ?int $palettesCount = null,
        public ?bool $oversizedPackage = null,
        public ?float $totalWeight = null,
        public ?array $parcels = null,
        public ?OrderParameters $parameters = null,
    ) {}
}
