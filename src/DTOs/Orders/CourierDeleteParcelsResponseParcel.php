<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Orders;

final readonly class CourierDeleteParcelsResponseParcel
{
    public function __construct(
        public string $barcode = '',
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            barcode: (string) ($data['barcode'] ?? ''),
        );
    }
}
