<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Orders;

use Daika7ana\Innoship\Support\ApiResponseMapper;
use Daika7ana\SimpleCollections\Collection;

/**
 * Reference this class for every response DTO: nullable-by-nature fields are
 * normalized in fromArray(), list fields are Collections.
 */
final readonly class OrderBarcodes
{
    /**
     * @param Collection<int, string> $additionalBarcodes
     */
    public function __construct(
        public int $sequenceNo = 0,
        public ?string $barcode = null,
        public ?string $customerBarcode = null,
        public Collection $additionalBarcodes = new Collection(),
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            sequenceNo: (int) ($data['sequenceNo'] ?? 0),
            barcode: isset($data['barcode']) ? (string) $data['barcode'] : null,
            customerBarcode: isset($data['customerBarcode']) ? (string) $data['customerBarcode'] : null,
            additionalBarcodes: new Collection(ApiResponseMapper::listOfStrings($data['additionalBarcodes'] ?? null)),
        );
    }
}
