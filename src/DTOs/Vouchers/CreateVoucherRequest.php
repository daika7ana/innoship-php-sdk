<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Vouchers;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Enums\Courier;
use Daika7ana\Innoship\Support\SerializesToArray;

/**
 * Voucher creation request.
 */
final readonly class CreateVoucherRequest implements Arrayable
{
    use SerializesToArray;

    public function __construct(
        public ?Courier $courier = null,
        public ?string $courierShipmentId = null,
        public ?Courier $voucherCourier = null,
        public float $insuranceAmount = 0.0,
        public ?string $insuranceCurrency = null,
    ) {}
}
