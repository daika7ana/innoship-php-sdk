<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Couriers;

final readonly class GenerateCourierLocationIdResponse
{
    public function __construct(
        public int $courierLocationId = 0,
        public bool $created = false,
        public bool $configured = false,
        public ?string $error = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            courierLocationId: (int) ($data['courierLocationId'] ?? 0),
            created: (bool) ($data['created'] ?? false),
            configured: (bool) ($data['configured'] ?? false),
            error: isset($data['error']) ? (string) $data['error'] : null,
        );
    }
}
