<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\OfflineOrders;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Support\SerializesToArray;

/**
 * Dimensions of an offline order parcel.
 */
final readonly class OfflineOrderParcelSize implements Arrayable
{
    use SerializesToArray;

    public function __construct(
        public float $width = 0.0,
        public float $height = 0.0,
        public float $length = 0.0,
    ) {}
}
