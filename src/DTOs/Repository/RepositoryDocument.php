<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Repository;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Support\SerializesToArray;

/**
 * Document stored in the repository.
 */
final readonly class RepositoryDocument implements Arrayable
{
    use SerializesToArray;

    public function __construct(
        public ?string $name = null,
        public ?string $contents = null,
    ) {}
}
