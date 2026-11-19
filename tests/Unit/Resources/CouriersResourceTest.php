<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Tests\Unit\Resources;

use Daika7ana\Innoship\DTOs\Couriers\CourierLocationAddress;
use Daika7ana\Innoship\DTOs\Couriers\GenerateCourierLocationIdRequest;
use Daika7ana\Innoship\DTOs\Couriers\PickupRequest;
use Daika7ana\Innoship\Enums\CallCourierPickupMode;
use Daika7ana\Innoship\Tests\Support\ResponseFixtureFactory;
use Daika7ana\Innoship\Tests\Support\TestClientFactory;
use PHPUnit\Framework\TestCase;

class CouriersResourceTest extends TestCase
{
    public function testRequestPickupSendsBodyAndMapsResponse(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json(['success' => true, 'message' => 'Pickup requested']),
        );

        $response = $client->couriers()->requestPickup(new PickupRequest(
            courierId: 3,
            locationId: 7,
            pickupMode: CallCourierPickupMode::FixedTime,
            lastPickupHour: 16,
            lastPickMinute: 30,
        ));

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/Courier/RequestPickup', $request->getUri()->getPath());
        $this->assertSame([
            'courierId' => 3,
            'locationId' => 7,
            'pickupMode' => 'FixedTime',
            'lastPickupHour' => 16,
            'lastPickMinute' => 30,
        ], json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR));

        $this->assertTrue($response->success);
        $this->assertSame('Pickup requested', $response->message);
    }

    public function testGenerateLocationIdSendsBody(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json([
                'courierLocationId' => 991,
                'created' => true,
                'configured' => false,
                'error' => null,
            ]),
        );

        $response = $client->couriers()->generateLocationId(
            4,
            2,
            new GenerateCourierLocationIdRequest(new CourierLocationAddress(name: 'Depot', countryCode: 'RO')),
        );

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/Courier/Location/GenerateId/4/2', $request->getUri()->getPath());
        $this->assertSame(
            ['address' => ['name' => 'Depot', 'countryCode' => 'RO']],
            json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR),
        );

        $this->assertSame(991, $response->courierLocationId);
        $this->assertTrue($response->created);
        $this->assertFalse($response->configured);
        $this->assertNull($response->error);
    }

    public function testGenerateLocationIdOmitsBodyWhenRequestIsNull(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json(['courierLocationId' => 1, 'created' => false, 'configured' => true]),
        );

        $client->couriers()->generateLocationId(4, 2);

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('/api/Courier/Location/GenerateId/4/2', $request->getUri()->getPath());
        $this->assertSame('', (string) $request->getBody());
    }

    public function testListLocationsUsesGetPathAndMapsLocations(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json([
                'locations' => [
                    [
                        'courierLocationId' => 10,
                        'name' => 'HQ',
                        'countryCode' => 'RO',
                        'countryName' => 'Romania',
                        'countyName' => 'Bucuresti',
                        'localityName' => 'Bucuresti',
                        'addressText' => 'Str. Test 1',
                        'postalCode' => '010101',
                        'contactPerson' => 'Jane',
                        'phone' => '0700000000',
                        'email' => 'jane@example.com',
                        'isDefault' => true,
                    ],
                ],
                'error' => null,
            ]),
        );

        $response = $client->couriers()->listLocations(4, 2);

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/Courier/Location/List/4/2', $request->getUri()->getPath());

        $this->assertCount(1, $response->locations);
        $location = $response->locations[0];
        $this->assertSame(10, $location->courierLocationId);
        $this->assertSame('HQ', $location->name);
        $this->assertSame('RO', $location->countryCode);
        $this->assertSame('jane@example.com', $location->email);
        $this->assertTrue($location->isDefault);
        $this->assertNull($response->error);
    }

    public function testSetLocationIdUsesPostPathAndMapsResponse(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json([
                'courierLocationId' => 55,
                'saved' => true,
                'error' => null,
            ]),
        );

        $response = $client->couriers()->setLocationId(4, 2, 55);

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/Courier/Location/SetId/4/2/55', $request->getUri()->getPath());
        $this->assertSame(55, $response->courierLocationId);
        $this->assertTrue($response->saved);
    }
}
