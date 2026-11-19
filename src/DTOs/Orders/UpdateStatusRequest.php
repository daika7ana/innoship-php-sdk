<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Orders;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Enums\Courier;
use Daika7ana\Innoship\Support\SerializesToArray;

final readonly class UpdateStatusRequest implements Arrayable
{
    use SerializesToArray;

    public function __construct(
        public ?Courier $courierId = null,
        public ?string $courierShipmentId = null,
        public ?int $typeId = null,
        public int $statusId = 0,
        public ?string $actionDateTime = null,
    ) {}
}
