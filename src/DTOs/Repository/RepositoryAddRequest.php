<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Repository;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Support\SerializesToArray;

/**
 * Documents to append to the repository for an external order.
 */
final readonly class RepositoryAddRequest implements Arrayable
{
    use SerializesToArray;

    /**
     * @param list<RepositoryDocument>|null $documents
     */
    public function __construct(
        public ?string $externalOrderId = null,
        public ?array $documents = null,
    ) {}
}
