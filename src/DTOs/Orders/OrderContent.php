<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Orders;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Support\SerializesToArray;

final readonly class OrderContent implements Arrayable
{
    use SerializesToArray;

    /**
     * @param list<OrderParcel>|null $parcels
     * @param list<OrderArticle>|null $articles
     */
    public function __construct(
        public ?int $envelopeCount = null,
        public ?int $parcelsCount = null,
        public ?int $palettesCount = null,
        public ?float $totalWeight = null,
        public ?string $contents = null,
        public ?string $package = null,
        public ?bool $oversizedPackage = null,
        public ?array $parcels = null,
        public ?string $invoiceNumber = null,
        public ?string $invoiceDate = null,
        public ?float $invoiceValue = null,
        public ?string $invoiceValueCurrency = null,
        public ?array $articles = null,
    ) {}
}
