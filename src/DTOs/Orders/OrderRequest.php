<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Orders;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Enums\Courier;
use Daika7ana\Innoship\Enums\PaymentType;
use Daika7ana\Innoship\Support\SerializesToArray;

final readonly class OrderRequest implements Arrayable
{
    use SerializesToArray;

    /**
     * @param array<string, ?string>|null $customAttributes
     * @param array<string, mixed>|null $dynamicCourierData
     */
    public function __construct(
        public ?int $serviceId = null,
        public ?Courier $courierId = null,
        public ?string $courierShipmentId = null,
        public ?string $courierDispatchId = null,
        public ?string $shipmentDate = null,
        public ?string $shipmentDateEnd = null,
        public ?OrderRequestAddress $addressFrom = null,
        public ?OrderRequestAddress $addressTo = null,
        public ?OrderRequestAddress $addressReturn = null,
        public ?PaymentType $payment = null,
        public ?OrderContent $content = null,
        public ?OrderExtra $extra = null,
        public ?OrderParameters $parameters = null,
        public ?string $externalClientLocation = null,
        public ?string $externalOrderId = null,
        public ?string $metadata = null,
        public ?string $sourceChannel = null,
        public ?string $observation = null,
        public ?array $customAttributes = null,
        public ?array $dynamicCourierData = null,
        public ?OrderClientSettings $clientSettings = null,
    ) {}
}
