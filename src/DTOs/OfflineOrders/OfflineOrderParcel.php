<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\OfflineOrders;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Enums\ParcelType;
use Daika7ana\Innoship\Support\SerializesToArray;

/**
 * Parcel of an offline order add request.
 */
final readonly class OfflineOrderParcel implements Arrayable
{
    use SerializesToArray;

    public function __construct(
        public int $sequenceNo = 0,
        public ?OfflineOrderParcelSize $size = null,
        public float $weight = 0.0,
        public ?ParcelType $type = null,
        public ?string $reference1 = null,
        public ?string $reference2 = null,
        public ?string $reference3 = null,
        public ?string $customerBarcode = null,
    ) {}
}
