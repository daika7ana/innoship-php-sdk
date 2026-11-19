<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Prices;

use Daika7ana\Innoship\Support\ApiResponseMapper;

final readonly class PriceRate
{
    /**
     * @param array<string, float> $priceComponents
     */
    public function __construct(
        public int $carrierId = 0,
        public ?string $carrier = null,
        public float $rateAmount = 0.0,
        public float $rateVatAmount = 0.0,
        public float $rateTotalAmount = 0.0,
        public ?string $rateCurrency = null,
        public float $score = 0.0,
        public float $performance = 0.0,
        public ?int $calculatedBusinessDays = null,
        public ?string $calculatedDeliveryDate = null,
        public int $deliveryDays = 0,
        public int $serviceId = 0,
        public ?string $service = null,
        public ?string $optionId = null,
        public ?string $optionName = null,
        public int $priority = 0,
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
            carrierId: (int) ($data['carrierId'] ?? 0),
            carrier: isset($data['carrier']) ? (string) $data['carrier'] : null,
            rateAmount: (float) ($data['rateAmount'] ?? 0.0),
            rateVatAmount: (float) ($data['rateVatAmount'] ?? 0.0),
            rateTotalAmount: (float) ($data['rateTotalAmount'] ?? 0.0),
            rateCurrency: isset($data['rateCurrency']) ? (string) $data['rateCurrency'] : null,
            score: (float) ($data['score'] ?? 0.0),
            performance: (float) ($data['performance'] ?? 0.0),
            calculatedBusinessDays: isset($data['calculatedBusinessDays']) ? (int) $data['calculatedBusinessDays'] : null,
            calculatedDeliveryDate: isset($data['calculatedDeliveryDate']) ? (string) $data['calculatedDeliveryDate'] : null,
            deliveryDays: (int) ($data['deliveryDays'] ?? 0),
            serviceId: (int) ($data['serviceId'] ?? 0),
            service: isset($data['service']) ? (string) $data['service'] : null,
            optionId: isset($data['optionId']) ? (string) $data['optionId'] : null,
            optionName: isset($data['optionName']) ? (string) $data['optionName'] : null,
            priority: (int) ($data['priority'] ?? 0),
            priceComponents: $components,
        );
    }
}
