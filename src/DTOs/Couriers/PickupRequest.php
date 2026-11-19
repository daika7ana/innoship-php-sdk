<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Couriers;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Enums\CallCourierPickupMode;
use Daika7ana\Innoship\Support\SerializesToArray;

/**
 * Request body for scheduling a courier pickup.
 */
final readonly class PickupRequest implements Arrayable
{
    use SerializesToArray;

    public function __construct(
        public ?int $courierId = null,
        public ?int $locationId = null,
        public ?CallCourierPickupMode $pickupMode = null,
        public ?int $lastPickupHour = null,
        public ?int $lastPickMinute = null,
    ) {}
}
