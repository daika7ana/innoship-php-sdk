<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Locations;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Support\SerializesToArray;

final readonly class LogoRequest implements Arrayable
{
    use SerializesToArray;

    public function __construct(
        public ?string $base64Logo = null,
    ) {}
}
