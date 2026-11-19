<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Couriers;

use Daika7ana\Innoship\Support\ApiResponseMapper;
use Daika7ana\SimpleCollections\Collection;

final readonly class CourierLocationsResponse
{
    /**
     * @param Collection<int, CourierLocationInfo> $locations
     */
    public function __construct(
        public Collection $locations = new Collection(),
        public ?string $error = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            locations: new Collection(array_map(
                static fn(array $item): CourierLocationInfo => CourierLocationInfo::fromArray($item),
                ApiResponseMapper::listOfArrays($data['locations'] ?? null),
            )),
            error: isset($data['error']) ? (string) $data['error'] : null,
        );
    }
}
