<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Tests\Unit\Exceptions;

use Daika7ana\Innoship\DTOs\Common\Errors\InnoshipError;
use Daika7ana\Innoship\Exceptions\UnexpectedStatusException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class UnexpectedStatusExceptionTest extends TestCase
{
    public function testAcceptsNoResponse(): void
    {
        $exception = new UnexpectedStatusException();

        $this->assertNull($exception->response);
        $this->assertNull($exception->error);
        $this->assertSame('Unexpected HTTP status', $exception->getMessage());
    }

    public function testDerivesMessageFromResponseStatusAndCarriesError(): void
    {
        $error = InnoshipError::fromArray(['message' => 'Server exploded'], 500);
        $exception = new UnexpectedStatusException(new Response(500), error: $error);

        $this->assertSame('Unexpected HTTP status 500', $exception->getMessage());
        $this->assertSame($error, $exception->error);
        $this->assertNotNull($exception->response);
    }
}
