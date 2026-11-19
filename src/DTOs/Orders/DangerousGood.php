<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Orders;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Support\SerializesToArray;

final readonly class DangerousGood implements Arrayable
{
    use SerializesToArray;

    public function __construct(
        public ?string $productId = null,
        public ?string $productDescription = null,
        public int $productClass = 0,
        public ?string $quantityClassification = null,
        public ?string $productQuantity = null,
        public ?string $capacityValue = null,
        public ?string $capacityUnitOfMeasurement = null,
        public ?string $packagingType = null,
        public ?string $packagingGroupType = null,
        public ?string $packagingInstructions = null,
        public ?string $chemicalRecordIdentifier = null,
    ) {}
}
