<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Orders;

use Daika7ana\Innoship\DTOs\Common\ErrorResponseItem;
use Daika7ana\Innoship\Support\ApiResponseMapper;
use Daika7ana\SimpleCollections\Collection;

final readonly class OrderSimulationResult
{
    /**
     * @param Collection<int, ErrorResponseItem> $errors
     */
    public function __construct(
        public ?OrderResponse $response = null,
        public Collection $errors = new Collection(),
        public ?string $correlationId = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            response: isset($data['response']) && is_array($data['response']) ? OrderResponse::fromArray($data['response']) : null,
            errors: new Collection(array_map(
                static fn(array $item) => ErrorResponseItem::fromArray($item),
                ApiResponseMapper::listOfArrays($data['errors'] ?? null),
            )),
            correlationId: isset($data['correlationId']) ? (string) $data['correlationId'] : null,
        );
    }
}
