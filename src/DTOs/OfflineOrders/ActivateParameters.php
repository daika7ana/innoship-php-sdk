<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\OfflineOrders;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Support\SerializesToArray;

/**
 * Activate request parameters.
 */
final readonly class ActivateParameters implements Arrayable
{
    use SerializesToArray;

    public function __construct(
        public bool $deleteMissingParcels = false,
        public ?bool $immediateActivation = null,
    ) {}
}
