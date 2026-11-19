<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Notifications;

final readonly class NotificationHistoryAttachment
{
    public function __construct(
        public ?string $name = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: isset($data['name']) ? (string) $data['name'] : null,
        );
    }
}
