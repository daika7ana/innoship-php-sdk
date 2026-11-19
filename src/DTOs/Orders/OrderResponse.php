<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Orders;

use Daika7ana\Innoship\Support\ApiResponseMapper;
use Daika7ana\SimpleCollections\Collection;

final readonly class OrderResponse
{
    /**
     * @param Collection<int, OrderBarcodes> $courierParcelsBarcodes
     * @param array<string, mixed> $courierResponse
     * @param array<string, mixed> $extra
     */
    public function __construct(
        public int|string|null $clientOrderId = null,
        public ?string $courierShipmentId = null,
        public ?string $courierShipmentVoucher = null,
        public ?string $externalOrderId = null,
        public int $courier = 0,
        public ?string $courierServiceName = null,
        public Collection $courierParcelsBarcodes = new Collection(),
        public ?OrderResponsePrice $price = null,
        public ?string $calculatedDeliveryDate = null,
        public ?string $trackPageUrl = null,
        public ?string $trackingNumber = null,
        public ?string $courierTrackPageUrl = null,
        public ?string $returnToLockerCode = null,
        public array $courierResponse = [],
        public array $extra = [],
        public ?OrderResponseAddress $address = null,
        public ?bool $receiverFixedLocationOverwrite = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            clientOrderId: isset($data['clientOrderId'])
                ? (is_int($data['clientOrderId']) ? $data['clientOrderId'] : (string) $data['clientOrderId'])
                : null,
            courierShipmentId: isset($data['courierShipmentId']) ? (string) $data['courierShipmentId'] : null,
            courierShipmentVoucher: isset($data['courierShipmentVoucher']) ? (string) $data['courierShipmentVoucher'] : null,
            externalOrderId: isset($data['externalOrderId']) ? (string) $data['externalOrderId'] : null,
            courier: (int) ($data['courier'] ?? 0),
            courierServiceName: isset($data['courierServiceName']) ? (string) $data['courierServiceName'] : null,
            courierParcelsBarcodes: new Collection(array_map(
                static fn(array $item) => OrderBarcodes::fromArray($item),
                ApiResponseMapper::listOfArrays($data['courierParcelsBarcodes'] ?? null),
            )),
            price: isset($data['price']) && is_array($data['price']) ? OrderResponsePrice::fromArray($data['price']) : null,
            calculatedDeliveryDate: isset($data['calculatedDeliveryDate']) ? (string) $data['calculatedDeliveryDate'] : null,
            trackPageUrl: isset($data['trackPageUrl']) ? (string) $data['trackPageUrl'] : null,
            trackingNumber: isset($data['trackingNumber']) ? (string) $data['trackingNumber'] : null,
            courierTrackPageUrl: isset($data['courierTrackPageUrl']) ? (string) $data['courierTrackPageUrl'] : null,
            returnToLockerCode: isset($data['returnToLockerCode']) ? (string) $data['returnToLockerCode'] : null,
            courierResponse: ApiResponseMapper::arrayOrEmpty($data['courierResponse'] ?? null),
            extra: ApiResponseMapper::arrayOrEmpty($data['extra'] ?? null),
            address: isset($data['address']) && is_array($data['address']) ? OrderResponseAddress::fromArray($data['address']) : null,
            receiverFixedLocationOverwrite: isset($data['receiverFixedLocationOverwrite']) ? (bool) $data['receiverFixedLocationOverwrite'] : null,
        );
    }
}
