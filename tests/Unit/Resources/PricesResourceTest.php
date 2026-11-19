<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Tests\Unit\Resources;

use Daika7ana\Innoship\DTOs\Orders\OrderRequest;
use Daika7ana\Innoship\DTOs\Orders\OrderRequestAddress;
use Daika7ana\Innoship\Tests\Support\ResponseFixtureFactory;
use Daika7ana\Innoship\Tests\Support\TestClientFactory;
use PHPUnit\Framework\TestCase;

class PricesResourceTest extends TestCase
{
    public function testGetPricesSendsOrderAndMapsRates(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json([
                'rates' => [
                    [
                        'carrierId' => 1,
                        'carrier' => 'DPD',
                        'rateAmount' => 13.36,
                        'rateVatAmount' => 2.54,
                        'rateTotalAmount' => 15.9,
                        'rateCurrency' => 'RON',
                        'score' => 0.9,
                        'performance' => 0.8,
                        'calculatedBusinessDays' => 2,
                        'calculatedDeliveryDate' => '2019-10-03T00:00:00Z',
                        'deliveryDays' => 2,
                        'serviceId' => 10,
                        'service' => 'Standard',
                        'optionId' => null,
                        'optionName' => null,
                        'priority' => 1,
                        'priceComponents' => ['FuelSurcharge' => 1.2],
                    ],
                ],
                'correlationId' => 'corr-1',
            ], 201),
        );

        $order = new OrderRequest(
            addressTo: new OrderRequestAddress(name: 'Jane Doe', country: 'RO'),
        );

        $response = $client->prices()->getPrices($order);

        $this->assertSame('corr-1', $response->correlationId);
        $this->assertCount(1, $response->rates);
        $this->assertSame('DPD', $response->rates[0]->carrier);
        $this->assertSame(15.9, $response->rates[0]->rateTotalAmount);
        $this->assertSame(1.2, $response->rates[0]->priceComponents['FuelSurcharge']);

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/Price', $request->getUri()->getPath());

        $body = json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('Jane Doe', $body['addressTo']['name']);
        $this->assertArrayNotHasKey('addressFrom', $body);
    }
}
