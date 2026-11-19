<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Orders;

use Daika7ana\Innoship\Support\ApiResponseMapper;

final readonly class OrderResponsePrice
{
    /**
     * @param array<string, float> $priceComponents
     */
    public function __construct(
        public float $amount = 0.0,
        public float $vat = 0.0,
        public float $totalAmount = 0.0,
        public ?string $currency = null,
        public array $priceComponents = [],
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $components = [];

        foreach (ApiResponseMapper::arrayOrEmpty($data['priceComponents'] ?? null) as $key => $value) {
            if (is_string($key) && is_numeric($value)) {
                $components[$key] = (float) $value;
            }
        }

        return new self(
            amount: (float) ($data['amount'] ?? 0.0),
            vat: (float) ($data['vat'] ?? 0.0),
            totalAmount: (float) ($data['totalAmount'] ?? 0.0),
            currency: isset($data['currency']) ? (string) $data['currency'] : null,
            priceComponents: $components,
        );
    }
}
