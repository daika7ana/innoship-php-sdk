<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Orders;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Enums\Courier;
use Daika7ana\Innoship\Support\SerializesToArray;

final readonly class UploadDocumentsRequest implements Arrayable
{
    use SerializesToArray;

    /**
     * @param list<CustomsDeclarationAttachment>|null $attachments
     */
    public function __construct(
        public ?Courier $courierId = null,
        public ?string $courierShipmentId = null,
        public ?array $attachments = null,
    ) {}
}
