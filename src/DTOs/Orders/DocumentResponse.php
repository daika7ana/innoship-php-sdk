<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Orders;

use Daika7ana\Innoship\Support\ApiResponseMapper;
use Daika7ana\SimpleCollections\Collection;

final readonly class DocumentResponse
{
    /**
     * @param Collection<int, string> $documents
     */
    public function __construct(
        public int $courier = 0,
        public ?string $contents = null,
        public Collection $documents = new Collection(),
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            courier: (int) ($data['courier'] ?? 0),
            contents: isset($data['contents']) ? (string) $data['contents'] : null,
            documents: new Collection(ApiResponseMapper::listOfStrings($data['documents'] ?? null)),
        );
    }
}
