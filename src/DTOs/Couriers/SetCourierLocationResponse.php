<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Couriers;

final readonly class SetCourierLocationResponse
{
    public function __construct(
        public int $courierLocationId = 0,
        public bool $saved = false,
        public ?string $error = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            courierLocationId: (int) ($data['courierLocationId'] ?? 0),
            saved: (bool) ($data['saved'] ?? false),
            error: isset($data['error']) ? (string) $data['error'] : null,
        );
    }
}
