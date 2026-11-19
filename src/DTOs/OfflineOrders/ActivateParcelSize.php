<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\OfflineOrders;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Support\SerializesToArray;

/**
 * Dimensions of an activated parcel.
 */
final readonly class ActivateParcelSize implements Arrayable
{
    use SerializesToArray;

    public function __construct(
        public ?float $width = null,
        public ?float $height = null,
        public ?float $length = null,
    ) {}
}
