<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Couriers;

final readonly class PickupResponse
{
    public function __construct(
        public bool $success = false,
        public ?string $message = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            success: (bool) ($data['success'] ?? false),
            message: isset($data['message']) ? (string) $data['message'] : null,
        );
    }
}
