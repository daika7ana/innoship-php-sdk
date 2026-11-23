<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Common\Errors;

final readonly class InnoshipError
{
    /**
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public int $code,
        public string $message,
        public string $id,
        public array $raw,
        public ?string $context = null,
        public ?string $component = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data, int $fallbackCode = 0): self
    {
        $rawErrors = is_array($data['errors'] ?? null) ? $data['errors'] : [];
        $firstRawError = array_is_list($rawErrors) ? ($rawErrors[0] ?? null) : null;
        $firstError = is_array($firstRawError) ? $firstRawError : [];

        $message = $data['message'] ?? $data['error'] ?? $data['general_error'] ?? $firstError['message'] ?? 'Innoship API error.';

        return new self(
            code: (int) ($data['code'] ?? $fallbackCode),
            message: is_string($message) ? $message : 'Innoship API error.',
            id: (string) ($data['id'] ?? $data['correlationId'] ?? ''),
            raw: $data,
            context: isset($firstError['categoryCode']) ? (string) $firstError['categoryCode'] : null,
            component: isset($firstError['subcategoryCode']) ? (string) $firstError['subcategoryCode'] : null,
        );
    }

    public function isValidationRelated(): bool
    {
        if ($this->code === 400 || $this->code === 422) {
            return true;
        }

        return isset($this->raw['errors']) && is_array($this->raw['errors']) && $this->raw['errors'] !== [];
    }

    public function isAuthenticationRelated(): bool
    {
        if ($this->code === 401 || $this->code === 403) {
            return true;
        }

        $message = strtolower($this->message);

        return str_contains($message, 'auth')
            || str_contains($message, 'password')
            || str_contains($message, 'credential')
            || str_contains($message, 'token')
            || str_contains($message, 'unauthenticated')
            || str_contains($message, 'api key')
            || str_contains($message, 'logged user')
            || str_contains($message, 'access rights');
    }
}
