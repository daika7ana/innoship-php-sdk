<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Labels;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Enums\Courier;
use Daika7ana\Innoship\Support\SerializesToArray;

/**
 * One AWB entry of a bulk label request.
 */
final readonly class BulkLabelItem implements Arrayable
{
    use SerializesToArray;

    public function __construct(
        public ?Courier $courierId = null,
        public ?int $typeId = null,
        public ?string $courierShipmentId = null,
    ) {}
}
