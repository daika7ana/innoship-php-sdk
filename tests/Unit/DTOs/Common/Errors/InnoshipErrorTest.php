<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Tests\Unit\DTOs\Common\Errors;

use Daika7ana\Innoship\DTOs\Common\Errors\InnoshipError;
use PHPUnit\Framework\TestCase;

class InnoshipErrorTest extends TestCase
{
    public function testFromArrayMapsInnoshipPayload(): void
    {
        $payload = [
            'errors' => [
                [
                    'message' => 'Invalid order data',
                    'details' => ['addressTo is required'],
                    'categoryCode' => 'Validation',
                    'subcategoryCode' => 'Order',
                ],
            ],
            'correlationId' => 'corr-123',
        ];

        $error = InnoshipError::fromArray($payload, 422);

        $this->assertSame(422, $error->code);
        $this->assertSame('Invalid order data', $error->message);
        $this->assertSame('corr-123', $error->id);
        $this->assertSame($payload, $error->raw);
        $this->assertSame('Validation', $error->context);
        $this->assertSame('Order', $error->component);
        $this->assertTrue($error->isValidationRelated());
        $this->assertFalse($error->isAuthenticationRelated());
    }

    public function testFromArrayFallsBackToTopLevelMessageAndDefaults(): void
    {
        $this->assertSame('Bad request', InnoshipError::fromArray(['message' => 'Bad request'], 400)->message);
        $this->assertSame('Innoship API error.', InnoshipError::fromArray([], 500)->message);
        $this->assertSame(500, InnoshipError::fromArray([], 500)->code);
        $this->assertSame('', InnoshipError::fromArray([])->id);
        $this->assertNull(InnoshipError::fromArray([])->context);
        $this->assertNull(InnoshipError::fromArray([])->component);
    }

    public function testAuthenticationRelatedByStatusAndMessage(): void
    {
        $this->assertTrue(InnoshipError::fromArray([], 401)->isAuthenticationRelated());
        $this->assertTrue(InnoshipError::fromArray([], 403)->isAuthenticationRelated());
        $this->assertTrue(InnoshipError::fromArray(['message' => 'Invalid API key'], 500)->isAuthenticationRelated());
        $this->assertFalse(InnoshipError::fromArray(['message' => 'Server exploded'], 500)->isAuthenticationRelated());
    }

    public function testValidationRelatedByStatusAndErrors(): void
    {
        $this->assertTrue(InnoshipError::fromArray([], 400)->isValidationRelated());
        $this->assertTrue(InnoshipError::fromArray([], 422)->isValidationRelated());
        $this->assertTrue(InnoshipError::fromArray(['errors' => [['message' => 'x']]], 500)->isValidationRelated());
        $this->assertFalse(InnoshipError::fromArray([], 500)->isValidationRelated());
    }
}
