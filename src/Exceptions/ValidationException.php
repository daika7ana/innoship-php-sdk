<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Exceptions;

use Daika7ana\Innoship\DTOs\Common\ErrorResponseItem;
use Exception;
use Psr\Http\Message\ResponseInterface;

class ValidationException extends InnoshipException
{
    /**
     * @param list<ErrorResponseItem> $errors
     */
    public function __construct(
        public readonly array $errors = [],
        string $message = '',
        public readonly ?string $correlationId = null,
        int $code = 0,
        ?Exception $previous = null,
        public readonly ?ResponseInterface $response = null,
        public readonly ?int $retryAfter = null,
    ) {
        parent::__construct($message ?: 'Validation failed', $code, $previous);
    }
}
