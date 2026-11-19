<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Exceptions;

use Psr\Http\Message\ResponseInterface;

class UnexpectedStatusException extends InnoshipException
{
    public function __construct(
        public readonly ResponseInterface $response,
        string $message = '',
        int $code = 0,
    ) {
        parent::__construct(
            $message ?: "Unexpected HTTP status {$response->getStatusCode()}",
            $code,
        );
    }
}
