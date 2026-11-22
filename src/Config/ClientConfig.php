<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Config;

final class ClientConfig
{
    public const BASE_URL = 'https://api.innoship.com';

    /**
     * @param float|null $timeout Request timeout in seconds; null lets the bundled transport apply its 30 s request / 10 s connect defaults.
     */
    public function __construct(
        public string $baseUrl = self::BASE_URL,
        public string $apiKey = '',
        public string $apiVersion = '1.0',
        public string $acceptLanguage = 'en',
        public ?float $timeout = null,
    ) {}

    public function withApiKey(string $apiKey): self
    {
        $config = clone $this;
        $config->apiKey = $apiKey;

        return $config;
    }

    public function withTimeout(?float $timeout): self
    {
        $config = clone $this;
        $config->timeout = $timeout;

        return $config;
    }

    public static function fromEnvironment(): self
    {
        return new self(apiKey: (string) (getenv('INNOSHIP_API_KEY') ?: ''));
    }
}
