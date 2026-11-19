<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Orders;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Support\SerializesToArray;

final readonly class OrderParcelSize implements Arrayable
{
    use SerializesToArray;

    public function __construct(
        public ?float $width = null,
        public ?float $height = null,
        public ?float $length = null,
    ) {}
}
