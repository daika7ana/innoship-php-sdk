<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Prices;

use Daika7ana\Innoship\Support\ApiResponseMapper;
use Daika7ana\SimpleCollections\Collection;

final readonly class PriceResponse
{
    /**
     * @param Collection<int, PriceRate> $rates
     */
    public function __construct(
        public Collection $rates = new Collection(),
        public ?string $correlationId = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            rates: new Collection(array_map(
                static fn(array $item) => PriceRate::fromArray($item),
                ApiResponseMapper::listOfArrays($data['rates'] ?? null),
            )),
            correlationId: isset($data['correlationId']) ? (string) $data['correlationId'] : null,
        );
    }
}
