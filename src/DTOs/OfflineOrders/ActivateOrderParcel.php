<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\OfflineOrders;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Support\SerializesToArray;

/**
 * Parcel of an activate or edit request.
 */
final readonly class ActivateOrderParcel implements Arrayable
{
    use SerializesToArray;

    /**
     * @param array<string, mixed>|null $parcelReferences
     */
    public function __construct(
        public ?string $barcode = null,
        public ?int $sequenceNo = null,
        public ?ActivateParcelSize $size = null,
        public ?float $weight = null,
        public ?array $parcelReferences = null,
    ) {}
}
