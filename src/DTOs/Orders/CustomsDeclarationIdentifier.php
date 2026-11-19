<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Orders;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Enums\CustomsDeclarationIdentifierType;
use Daika7ana\Innoship\Support\SerializesToArray;

final readonly class CustomsDeclarationIdentifier implements Arrayable
{
    use SerializesToArray;

    public function __construct(
        public ?string $value = null,
        public ?CustomsDeclarationIdentifierType $type = null,
    ) {}
}
