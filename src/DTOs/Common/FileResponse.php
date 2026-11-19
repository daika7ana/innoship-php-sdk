<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Common;

use Daika7ana\Innoship\Support\ApiResponseMapper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Raw file response used by label and manifest downloads.
 */
final readonly class FileResponse
{
    public function __construct(
        public StreamInterface $stream,
        public string $contentType = '',
        public ?string $filename = null,
    ) {}

    public function contents(): string
    {
        return (string) $this->stream;
    }

    /**
     * @throws \Daika7ana\Innoship\Exceptions\AuthenticationException
     * @throws \Daika7ana\Innoship\Exceptions\UnexpectedStatusException
     * @throws \Daika7ana\Innoship\Exceptions\ValidationException
     */
    public static function fromResponse(ResponseInterface $response): self
    {
        ApiResponseMapper::assertStatus($response, 200);

        return new self(
            stream: $response->getBody(),
            contentType: $response->getHeaderLine('Content-Type'),
            filename: self::parseFilename($response->getHeaderLine('Content-Disposition')),
        );
    }

    private static function parseFilename(string $contentDisposition): ?string
    {
        if (preg_match("/filename\*=UTF-8''([^;]+)/i", $contentDisposition, $matches) === 1) {
            return rawurldecode(trim($matches[1]));
        }

        if (preg_match('/filename="?([^";]+)"?/i', $contentDisposition, $matches) === 1) {
            return trim($matches[1]);
        }

        return null;
    }
}
