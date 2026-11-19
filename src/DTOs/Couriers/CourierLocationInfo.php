<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Couriers;

final readonly class CourierLocationInfo
{
    public function __construct(
        public int $courierLocationId = 0,
        public ?string $name = null,
        public ?string $countryCode = null,
        public ?string $countryName = null,
        public ?string $countyName = null,
        public ?string $localityName = null,
        public ?string $addressText = null,
        public ?string $postalCode = null,
        public ?string $contactPerson = null,
        public ?string $phone = null,
        public ?string $email = null,
        public bool $isDefault = false,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            courierLocationId: (int) ($data['courierLocationId'] ?? 0),
            name: isset($data['name']) ? (string) $data['name'] : null,
            countryCode: isset($data['countryCode']) ? (string) $data['countryCode'] : null,
            countryName: isset($data['countryName']) ? (string) $data['countryName'] : null,
            countyName: isset($data['countyName']) ? (string) $data['countyName'] : null,
            localityName: isset($data['localityName']) ? (string) $data['localityName'] : null,
            addressText: isset($data['addressText']) ? (string) $data['addressText'] : null,
            postalCode: isset($data['postalCode']) ? (string) $data['postalCode'] : null,
            contactPerson: isset($data['contactPerson']) ? (string) $data['contactPerson'] : null,
            phone: isset($data['phone']) ? (string) $data['phone'] : null,
            email: isset($data['email']) ? (string) $data['email'] : null,
            isDefault: (bool) ($data['isDefault'] ?? false),
        );
    }
}
