<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Orders;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Enums\UploadDocumentType;
use Daika7ana\Innoship\Support\SerializesToArray;

final readonly class CustomsDeclarationAttachment implements Arrayable
{
    use SerializesToArray;

    public function __construct(
        public ?string $name = null,
        public ?string $content = null,
        public ?UploadDocumentType $documentType = null,
    ) {}
}
