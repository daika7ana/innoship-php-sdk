<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Tests\Unit\Resources;

use Daika7ana\Innoship\DTOs\OfflineOrders\ActivateAddress;
use Daika7ana\Innoship\DTOs\OfflineOrders\ActivateOrderParcel;
use Daika7ana\Innoship\DTOs\OfflineOrders\ActivateOrderRequest;
use Daika7ana\Innoship\DTOs\OfflineOrders\ActivateParameters;
use Daika7ana\Innoship\DTOs\OfflineOrders\ActivateParcelSize;
use Daika7ana\Innoship\DTOs\OfflineOrders\EditOrderRequest;
use Daika7ana\Innoship\DTOs\OfflineOrders\OfflineOrderAddRequest;
use Daika7ana\Innoship\DTOs\OfflineOrders\OfflineOrderParcel;
use Daika7ana\Innoship\DTOs\OfflineOrders\OfflineOrderParcelSize;
use Daika7ana\Innoship\Enums\Courier;
use Daika7ana\Innoship\Enums\OrderOfflineStatus;
use Daika7ana\Innoship\Enums\ParcelType;
use Daika7ana\Innoship\Tests\Support\ResponseFixtureFactory;
use Daika7ana\Innoship\Tests\Support\TestClientFactory;
use PHPUnit\Framework\TestCase;

class OfflineOrdersResourceTest extends TestCase
{
    public function testAddSendsJsonListOfOrders(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json([
                'data' => [['courierShipmentId' => 'SHIP-1']],
            ]),
        );

        $result = $client->offlineOrders()->add([
            new OfflineOrderAddRequest(
                courier: Courier::DPD,
                courierShipmentId: 'SHIP-1',
                finalParcel: true,
                parcels: [
                    new OfflineOrderParcel(
                        sequenceNo: 1,
                        size: new OfflineOrderParcelSize(width: 10.0, height: 20.0, length: 30.0),
                        weight: 2.5,
                        type: ParcelType::Parcel,
                        customerBarcode: 'CUST-1',
                    ),
                ],
            ),
        ]);

        $request = $httpClient->lastRequest;

        $this->assertNotNull($request);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/OfflineOrder/Add', $request->getUri()->getPath());
        $this->assertSame([
            [
                'courier' => 'DPD',
                'courierShipmentId' => 'SHIP-1',
                'finalParcel' => true,
                'parcels' => [
                    [
                        'sequenceNo' => 1,
                        'size' => ['width' => 10, 'height' => 20, 'length' => 30],
                        'weight' => 2.5,
                        'type' => 'Parcel',
                        'customerBarcode' => 'CUST-1',
                    ],
                ],
            ],
        ], json_decode((string) $request->getBody(), true, flags: JSON_THROW_ON_ERROR));
        $this->assertSame(['data' => [['courierShipmentId' => 'SHIP-1']]], $result);
    }

    public function testEditSendsJsonListOfOrders(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json(['data' => []]),
        );

        $result = $client->offlineOrders()->edit([
            new EditOrderRequest(
                offlineStatusId: OrderOfflineStatus::Success,
                courier: Courier::DPD,
                courierShipmentId: 'SHIP-1',
                parameters: new ActivateParameters(deleteMissingParcels: true),
            ),
        ]);

        $request = $httpClient->lastRequest;

        $this->assertNotNull($request);
        $this->assertSame('PUT', $request->getMethod());
        $this->assertSame('/api/OfflineOrder/Edit', $request->getUri()->getPath());
        $this->assertSame([
            [
                'offlineStatusId' => 'Success',
                'courier' => 'DPD',
                'courierShipmentId' => 'SHIP-1',
                'parameters' => ['deleteMissingParcels' => true],
            ],
        ], json_decode((string) $request->getBody(), true, flags: JSON_THROW_ON_ERROR));
        $this->assertSame(['data' => []], $result);
    }

    public function testActivateSendsJsonListOfOrders(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json(['data' => []]),
        );

        $result = $client->offlineOrders()->activate([
            new ActivateOrderRequest(
                courier: Courier::DPD,
                courierShipmentId: 'SHIP-1',
                shipmentDate: '2026-01-01',
                parcels: [
                    new ActivateOrderParcel(
                        barcode: 'BC-1',
                        sequenceNo: 1,
                        size: new ActivateParcelSize(width: 10.0, height: 20.0, length: 30.0),
                        weight: 2.5,
                        parcelReferences: ['ref' => 'value'],
                    ),
                ],
                addressFrom: new ActivateAddress(name: 'Sender', countyId: 1),
                addressTo: new ActivateAddress(name: 'Receiver', countyId: 2),
                parameters: new ActivateParameters(immediateActivation: true),
            ),
        ]);

        $request = $httpClient->lastRequest;

        $this->assertNotNull($request);
        $this->assertSame('PUT', $request->getMethod());
        $this->assertSame('/api/OfflineOrder/Activate', $request->getUri()->getPath());
        $this->assertSame([
            [
                'courier' => 'DPD',
                'courierShipmentId' => 'SHIP-1',
                'shipmentDate' => '2026-01-01',
                'parcels' => [
                    [
                        'barcode' => 'BC-1',
                        'sequenceNo' => 1,
                        'size' => ['width' => 10, 'height' => 20, 'length' => 30],
                        'weight' => 2.5,
                        'parcelReferences' => ['ref' => 'value'],
                    ],
                ],
                'addressFrom' => ['name' => 'Sender', 'countyId' => 1],
                'addressTo' => ['name' => 'Receiver', 'countyId' => 2],
                'parameters' => ['deleteMissingParcels' => false, 'immediateActivation' => true],
            ],
        ], json_decode((string) $request->getBody(), true, flags: JSON_THROW_ON_ERROR));
        $this->assertSame(['data' => []], $result);
    }
}
