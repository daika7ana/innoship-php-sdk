<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Tests\Unit\DTOs;

use Daika7ana\Innoship\DTOs\Orders\OrderResponse;
use Daika7ana\Innoship\DTOs\Prices\PriceResponse;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Locks the money fields to payloads captured from the live API on 2026-11-19.
 * Re-capture the fixtures before changing expected values.
 */
class LivePayloadContractTest extends TestCase
{
    public function testPricePayloadMapsMoneyFields(): void
    {
        $response = PriceResponse::fromArray($this->fixture('price_response.json'));

        $this->assertSame('499e5311459da8ef168787d3649e6b03', $response->correlationId);
        $this->assertCount(1, $response->rates);

        $rate = $response->rates[0];
        $this->assertSame('DPD', $rate->carrier);
        $this->assertSame(8.4, $rate->rateAmount);
        $this->assertSame(1.76, $rate->rateVatAmount);
        $this->assertSame(10.16, $rate->rateTotalAmount);
        $this->assertSame('RON', $rate->rateCurrency);
        $this->assertGreaterThan(0.0, $rate->rateTotalAmount);
    }

    public function testOrderSimulationPayloadMapsPrice(): void
    {
        $order = OrderResponse::fromArray($this->fixture('order_simulate_response.json'));

        $this->assertSame('-3__', $order->clientOrderId);
        $this->assertSame(-3, $order->courier);
        $this->assertSame('Innoship Standard', $order->courierServiceName);

        $price = $order->price;

        if ($price === null) {
            throw new RuntimeException('Live simulate payload has no price.');
        }

        $this->assertSame(33.0, $price->amount);
        $this->assertSame(6.93, $price->vat);
        $this->assertSame(39.93, $price->totalAmount);
        $this->assertSame('RON', $price->currency);
        $this->assertGreaterThan(0.0, $price->totalAmount);
    }

    /**
     * @return array<string, mixed>
     */
    private function fixture(string $name): array
    {
        $contents = file_get_contents(__DIR__ . '/../../Fixtures/' . $name);
        $this->assertIsString($contents);

        $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($data);

        /** @var array<string, mixed> $data */
        return $data;
    }
}
