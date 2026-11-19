<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Vouchers;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Enums\Courier;
use Daika7ana\Innoship\Support\SerializesToArray;

/**
 * Voucher activation request.
 */
final readonly class ActivateVoucherRequest implements Arrayable
{
    use SerializesToArray;

    public function __construct(
        public ?Courier $courier = null,
        public ?string $courierShipmentId = null,
        public ?string $courierShipmentVoucher = null,
    ) {}
}
