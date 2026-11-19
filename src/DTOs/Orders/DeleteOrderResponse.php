<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Orders;

final readonly class DeleteOrderResponse
{
    public function __construct(
        public int $courier = 0,
        public bool $success = false,
        public ?string $message = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            courier: (int) ($data['courier'] ?? 0),
            success: (bool) ($data['success'] ?? false),
            message: isset($data['message']) ? (string) $data['message'] : null,
        );
    }
}
