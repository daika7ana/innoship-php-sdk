<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Locations;

use Daika7ana\Innoship\Support\ApiResponseMapper;
use Daika7ana\SimpleCollections\Collection;

final readonly class LogoResponse
{
    /**
     * @param Collection<int, string> $errors
     */
    public function __construct(
        public ?string $logoUrl = null,
        public bool $success = false,
        public bool $isSuccess = false,
        public Collection $errors = new Collection(),
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        // The API wraps the logo payload in a Result envelope: {"value": {...}, "isSuccess": ...}.
        $value = $data['value'] ?? null;
        $payload = is_array($value) ? $value : $data;

        return new self(
            logoUrl: isset($payload['logoUrl']) ? (string) $payload['logoUrl'] : null,
            success: (bool) ($payload['success'] ?? false),
            isSuccess: (bool) ($data['isSuccess'] ?? $payload['isSuccess'] ?? false),
            errors: new Collection(ApiResponseMapper::listOfStrings($data['errors'] ?? $payload['errors'] ?? null)),
        );
    }
}
