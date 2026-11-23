<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Exceptions;

use Daika7ana\Innoship\DTOs\Common\Errors\InnoshipError;
use Psr\Http\Message\ResponseInterface;

class UnexpectedStatusException extends InnoshipException
{
    public function __construct(
        public readonly ?ResponseInterface $response = null,
        string $message = '',
        int $code = 0,
        public readonly ?int $retryAfter = null,
        public readonly ?InnoshipError $error = null,
    ) {
        if ($message === '') {
            $message = $response === null
                ? 'Unexpected HTTP status'
                : "Unexpected HTTP status {$response->getStatusCode()}";
        }

        parent::__construct($message, $response?->getStatusCode() ?? $code);
    }
}
