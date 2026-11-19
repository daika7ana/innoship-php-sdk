<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Orders;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Enums\ParcelType;
use Daika7ana\Innoship\Support\SerializesToArray;

final readonly class OrderParcel implements Arrayable
{
    use SerializesToArray;

    /**
     * @param list<DangerousGood>|null $dangerousGoods
     * @param list<OrderArticle>|null $articles
     * @param array<string, ?string>|null $parcelReferences
     */
    public function __construct(
        public int $sequenceNo = 0,
        public ?string $barcode = null,
        public ?OrderParcelSize $size = null,
        public ?float $weight = null,
        public ?ParcelType $type = null,
        public ?string $reference1 = null,
        public ?string $reference2 = null,
        public ?string $reference3 = null,
        public ?string $customerBarcode = null,
        public ?array $dangerousGoods = null,
        public ?array $articles = null,
        public ?array $parcelReferences = null,
    ) {}
}
