<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Notifications;

use Daika7ana\Innoship\Support\ApiResponseMapper;
use Daika7ana\SimpleCollections\Collection;

final readonly class NotificationHistoryResponse
{
    /**
     * @param Collection<int, NotificationHistoryItem> $notifications
     */
    public function __construct(
        public Collection $notifications = new Collection(),
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            notifications: new Collection(array_map(
                static fn(array $item): NotificationHistoryItem => NotificationHistoryItem::fromArray($item),
                ApiResponseMapper::listOfArrays($data['notifications'] ?? null),
            )),
        );
    }
}
