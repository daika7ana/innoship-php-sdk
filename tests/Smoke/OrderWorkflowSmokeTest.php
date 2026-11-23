<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Tests\Smoke;

use Daika7ana\Innoship\DTOs\Orders\OrderResponse;
use PHPUnit\Framework\Attributes\Group;

final class OrderWorkflowSmokeTest extends SmokeTestCase
{
    #[Group('smoke')]
    public function testSimulateCreateAndDeleteOrderAgainstLiveApi(): void
    {
        $client = $this->client();
        $this->requireWrites();

        $request = $this->sampleOrderRequest();

        $simulation = $client->orders()->simulate($request);

        $this->assertInstanceOf(OrderResponse::class, $simulation);

        $order = $client->orders()->create($request);

        try {
            $this->assertGreaterThan(0, $order->courier);
            $this->assertNotNull($order->courierShipmentId);
            $this->assertNotSame('', (string) $order->courierShipmentId);
        } finally {
            $deleted = $client->orders()->delete($order->courier, (string) $order->courierShipmentId);

            $this->assertTrue($deleted->success, 'The smoke order could not be deleted: ' . ($deleted->message ?? 'unknown error'));
        }
    }
}
