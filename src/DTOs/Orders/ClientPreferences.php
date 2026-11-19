<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Orders;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Enums\LabelFormat;
use Daika7ana\Innoship\Support\SerializesToArray;

final readonly class ClientPreferences implements Arrayable
{
    use SerializesToArray;

    /**
     * @param list<string>|null $orderColumns
     * @param list<string>|null $pickupColumns
     * @param list<string>|null $returnColumns
     */
    public function __construct(
        public ?LabelFormat $preferredLabel = null,
        public ?int $preferredLocationId = null,
        public ?array $orderColumns = null,
        public ?array $pickupColumns = null,
        public ?array $returnColumns = null,
    ) {}
}
