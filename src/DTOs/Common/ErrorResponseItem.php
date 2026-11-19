<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Common;

use Daika7ana\SimpleCollections\Collection;

final readonly class ErrorResponseItem
{
    /**
     * @param Collection<int, string> $details
     */
    public function __construct(
        public string $message = '',
        public Collection $details = new Collection(),
        public ?string $categoryCode = null,
        public ?string $subcategoryCode = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $details = [];

        foreach (is_array($data['details'] ?? null) ? $data['details'] : [] as $detail) {
            if (is_scalar($detail)) {
                $details[] = (string) $detail;
            }
        }

        return new self(
            message: (string) ($data['message'] ?? ''),
            details: new Collection($details),
            categoryCode: isset($data['categoryCode']) ? (string) $data['categoryCode'] : null,
            subcategoryCode: isset($data['subcategoryCode']) ? (string) $data['subcategoryCode'] : null,
        );
    }
}
