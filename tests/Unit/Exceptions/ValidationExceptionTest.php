<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Tests\Unit\Exceptions;

use Daika7ana\Innoship\DTOs\Common\Errors\InnoshipError;
use Daika7ana\Innoship\Exceptions\ValidationException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ValidationExceptionTest extends TestCase
{
    public function testNamedArgumentsKeepTheSiblingShape(): void
    {
        $response = new Response(422);
        $error = InnoshipError::fromArray(['message' => 'Invalid order data'], 422);

        $exception = new ValidationException(
            response: $response,
            message: 'Invalid order data',
            code: 0,
            retryAfter: 5,
            error: $error,
            correlationId: 'corr-123',
        );

        $this->assertSame($response, $exception->response);
        $this->assertSame('Invalid order data', $exception->getMessage());
        $this->assertSame(422, $exception->getCode());
        $this->assertSame(5, $exception->retryAfter);
        $this->assertSame($error, $exception->error);
        $this->assertSame('corr-123', $exception->correlationId);
    }

    public function testCodeIsZeroWithoutResponse(): void
    {
        $exception = new ValidationException();

        $this->assertSame(0, $exception->getCode());
        $this->assertSame('Validation failed', $exception->getMessage());
    }
}
