<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Labels;

use Daika7ana\Innoship\Support\ApiResponseMapper;
use Daika7ana\SimpleCollections\Collection;

/**
 * Label payload returned by the label endpoints.
 */
final readonly class LabelResponse
{
    /**
     * @param Collection<int, string> $labels
     */
    public function __construct(
        public int $courier = 0,
        public ?string $contents = null,
        public Collection $labels = new Collection(),
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            courier: (int) ($data['courier'] ?? 0),
            contents: isset($data['contents']) ? (string) $data['contents'] : null,
            labels: new Collection(ApiResponseMapper::listOfStrings($data['labels'] ?? null)),
        );
    }
}
