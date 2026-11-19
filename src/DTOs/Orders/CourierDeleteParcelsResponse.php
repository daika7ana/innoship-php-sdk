<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Orders;

use Daika7ana\Innoship\Support\ApiResponseMapper;
use Daika7ana\SimpleCollections\Collection;

final readonly class CourierDeleteParcelsResponse
{
    /**
     * @param Collection<int, string> $messages
     * @param Collection<int, CourierDeleteParcelsResponseParcel> $parcels
     */
    public function __construct(
        public bool $success = false,
        public Collection $messages = new Collection(),
        public ?string $courierShipmentId = null,
        public Collection $parcels = new Collection(),
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            success: (bool) ($data['success'] ?? false),
            messages: new Collection(ApiResponseMapper::listOfStrings($data['messages'] ?? null)),
            courierShipmentId: isset($data['courierShipmentId']) ? (string) $data['courierShipmentId'] : null,
            parcels: new Collection(array_map(
                static fn(array $item) => CourierDeleteParcelsResponseParcel::fromArray($item),
                ApiResponseMapper::listOfArrays($data['parcels'] ?? null),
            )),
        );
    }
}
