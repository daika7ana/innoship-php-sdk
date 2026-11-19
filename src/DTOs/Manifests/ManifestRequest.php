<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Manifests;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Enums\Courier;
use Daika7ana\Innoship\Support\SerializesToArray;

/**
 * Request for a courier pickup manifest.
 */
final readonly class ManifestRequest implements Arrayable
{
    use SerializesToArray;

    public function __construct(
        public ?Courier $courierId = null,
        public ?string $externalLocationId = null,
        public ?int $lastPickHour = null,
    ) {}
}
