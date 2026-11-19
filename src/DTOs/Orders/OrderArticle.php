<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Orders;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Support\SerializesToArray;

final readonly class OrderArticle implements Arrayable
{
    use SerializesToArray;

    /**
     * @param array<string, ?string>|null $articleReferences
     */
    public function __construct(
        public int $quantity = 0,
        public ?string $description = null,
        public ?string $countryOfOrigin = null,
        public ?string $tariff = null,
        public float $weightNet = 0.0,
        public float $weightGross = 0.0,
        public float $pricePerPieceAmount = 0.0,
        public ?string $pricePerPieceAmountCurrency = null,
        public ?string $sku = null,
        public ?array $articleReferences = null,
    ) {}
}
