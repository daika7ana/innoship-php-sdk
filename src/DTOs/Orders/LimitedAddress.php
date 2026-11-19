<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Orders;

final readonly class LimitedAddress
{
    public function __construct(
        public ?string $country = null,
        public ?string $countyName = null,
        public ?string $localityName = null,
        public ?string $postalCode = null,
        public ?int $fixedLocationId = null,
        public ?string $fixedLocationName = null,
        public ?string $addressText = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            country: isset($data['country']) ? (string) $data['country'] : null,
            countyName: isset($data['countyName']) ? (string) $data['countyName'] : null,
            localityName: isset($data['localityName']) ? (string) $data['localityName'] : null,
            postalCode: isset($data['postalCode']) ? (string) $data['postalCode'] : null,
            fixedLocationId: isset($data['fixedLocationId']) ? (int) $data['fixedLocationId'] : null,
            fixedLocationName: isset($data['fixedLocationName']) ? (string) $data['fixedLocationName'] : null,
            addressText: isset($data['addressText']) ? (string) $data['addressText'] : null,
        );
    }
}
