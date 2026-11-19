<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Orders;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Support\SerializesToArray;

final readonly class OrderParameters implements Arrayable
{
    use SerializesToArray;

    public function __construct(
        public bool $async = false,
        public bool $getParcelsBarcodes = false,
        public bool $includeCourierResponse = false,
        public bool $includePriceBreakdown = false,
        public bool $includeAddressInResponse = false,
        public bool $consolidateOrder = false,
        public bool $ignoreNotifications = false,
        public bool $importExternalOrder = false,
        public bool $overwriteLocationAddressWithSenderDetails = false,
    ) {}
}
