<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Locations;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Support\SerializesToArray;

final readonly class ReturnPortalSettings implements Arrayable
{
    use SerializesToArray;

    public function __construct(
        public ?string $returnPortalUrl = null,
        public ?string $tenantId = null,
    ) {}
}
