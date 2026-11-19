<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Notifications;

use Daika7ana\Innoship\Support\ApiResponseMapper;
use Daika7ana\SimpleCollections\Collection;

final readonly class NotificationHistoryItem
{
    /**
     * @param Collection<int, NotificationHistoryAttachment> $attachments
     */
    public function __construct(
        public ?string $templateName = null,
        public ?string $shipmentAwb = null,
        public ?string $courier = null,
        public int $courierId = 0,
        public ?string $courierDisplayName = null,
        public int $orderStatusId = 0,
        public ?string $orderStatus = null,
        public ?string $orderStatusDate = null,
        public ?string $notificationDate = null,
        public int $count = 0,
        public bool $status = false,
        public ?string $message = null,
        public ?string $deliveryStatus = null,
        public Collection $attachments = new Collection(),
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            templateName: isset($data['templateName']) ? (string) $data['templateName'] : null,
            shipmentAwb: isset($data['shipmentAwb']) ? (string) $data['shipmentAwb'] : null,
            courier: isset($data['courier']) ? (string) $data['courier'] : null,
            courierId: (int) ($data['courierId'] ?? 0),
            courierDisplayName: isset($data['courierDisplayName']) ? (string) $data['courierDisplayName'] : null,
            orderStatusId: (int) ($data['orderStatusId'] ?? 0),
            orderStatus: isset($data['orderStatus']) ? (string) $data['orderStatus'] : null,
            orderStatusDate: isset($data['orderStatusDate']) ? (string) $data['orderStatusDate'] : null,
            notificationDate: isset($data['notificationDate']) ? (string) $data['notificationDate'] : null,
            count: (int) ($data['count'] ?? 0),
            status: (bool) ($data['status'] ?? false),
            message: isset($data['message']) ? (string) $data['message'] : null,
            deliveryStatus: isset($data['deliveryStatus']) ? (string) $data['deliveryStatus'] : null,
            attachments: new Collection(array_map(
                static fn(array $item): NotificationHistoryAttachment => NotificationHistoryAttachment::fromArray($item),
                ApiResponseMapper::listOfArrays($data['attachments'] ?? null),
            )),
        );
    }
}
