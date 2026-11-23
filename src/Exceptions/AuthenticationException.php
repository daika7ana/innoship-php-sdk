<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Exceptions;

use Daika7ana\Innoship\DTOs\Common\Errors\InnoshipError;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class AuthenticationException extends InnoshipException
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        public readonly ?ResponseInterface $response = null,
        public readonly ?int $retryAfter = null,
        public readonly ?InnoshipError $error = null,
    ) {
        parent::__construct($message, $response?->getStatusCode() ?? $code, $previous);
    }
}
