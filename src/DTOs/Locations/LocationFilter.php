<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Locations;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Support\SerializesToArray;

/**
 * Filter for the client location summary endpoint.
 */
final readonly class LocationFilter implements Arrayable
{
    use SerializesToArray;

    /**
     * @param list<string>|null $name
     * @param list<int>|null $countryId
     * @param list<int>|null $countyId
     * @param list<int>|null $localityId
     * @param list<int>|null $courierId
     * @param list<int>|null $ids
     * @param list<string>|null $userId
     */
    public function __construct(
        public ?array $name = null,
        public ?array $countryId = null,
        public ?array $countyId = null,
        public ?array $localityId = null,
        public ?string $addressText = null,
        public ?array $courierId = null,
        public ?array $ids = null,
        public ?string $createdAtLe = null,
        public ?string $createdAtGe = null,
        public ?array $userId = null,
        public ?int $skip = null,
        public ?int $take = null,
        public ?string $orderBy = null,
        public ?bool $orderByAsc = null,
    ) {}
}
