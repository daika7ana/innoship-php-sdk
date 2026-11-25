<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Exceptions;

use Daika7ana\Innoship\DTOs\Common\ErrorResponseItem;
use Daika7ana\Innoship\DTOs\Common\Errors\InnoshipError;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ValidationException extends InnoshipException
{
    /**
     * @param list<ErrorResponseItem> $errors
     */
    public function __construct(
        public readonly array $errors = [],
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        public readonly ?ResponseInterface $response = null,
        public readonly ?int $retryAfter = null,
        public readonly ?InnoshipError $error = null,
        public readonly ?string $correlationId = null,
    ) {
        parent::__construct($message ?: 'Validation failed', $response?->getStatusCode() ?? $code, $previous);
    }
}
