<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Tests\Unit\Resources;

use Daika7ana\Innoship\DTOs\Vouchers\ActivateVoucherRequest;
use Daika7ana\Innoship\DTOs\Vouchers\CreateVoucherRequest;
use Daika7ana\Innoship\Enums\Courier;
use Daika7ana\Innoship\Tests\Support\ResponseFixtureFactory;
use Daika7ana\Innoship\Tests\Support\TestClientFactory;
use PHPUnit\Framework\TestCase;

class VouchersResourceTest extends TestCase
{
    public function testCreateSendsRequest(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json(['data' => ['courierShipmentVoucher' => 'V-1']]),
        );

        $result = $client->vouchers()->create(new CreateVoucherRequest(
            courier: Courier::DPD,
            courierShipmentId: 'SHIP-1',
            voucherCourier: Courier::GLS,
            insuranceAmount: 100.0,
            insuranceCurrency: 'RON',
        ));

        $request = $httpClient->lastRequest;

        $this->assertNotNull($request);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/Voucher/Create', $request->getUri()->getPath());
        $this->assertSame([
            'courier' => 'DPD',
            'courierShipmentId' => 'SHIP-1',
            'voucherCourier' => 'GLS',
            'insuranceAmount' => 100,
            'insuranceCurrency' => 'RON',
        ], json_decode((string) $request->getBody(), true, flags: JSON_THROW_ON_ERROR));
        $this->assertSame(['data' => ['courierShipmentVoucher' => 'V-1']], $result);
    }

    public function testActivateSendsRequest(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json(['data' => []]),
        );

        $result = $client->vouchers()->activate(new ActivateVoucherRequest(
            courier: Courier::DPD,
            courierShipmentId: 'SHIP-1',
            courierShipmentVoucher: 'V-1',
        ));

        $request = $httpClient->lastRequest;

        $this->assertNotNull($request);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/Voucher/Activate', $request->getUri()->getPath());
        $this->assertSame([
            'courier' => 'DPD',
            'courierShipmentId' => 'SHIP-1',
            'courierShipmentVoucher' => 'V-1',
        ], json_decode((string) $request->getBody(), true, flags: JSON_THROW_ON_ERROR));
        $this->assertSame(['data' => []], $result);
    }
}
