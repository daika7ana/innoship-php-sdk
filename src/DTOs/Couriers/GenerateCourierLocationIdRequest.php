<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Couriers;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Support\SerializesToArray;

final readonly class GenerateCourierLocationIdRequest implements Arrayable
{
    use SerializesToArray;

    public function __construct(
        public ?CourierLocationAddress $address = null,
    ) {}
}
