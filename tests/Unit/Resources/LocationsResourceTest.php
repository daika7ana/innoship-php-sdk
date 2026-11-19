<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Tests\Unit\Resources;

use Daika7ana\Innoship\DTOs\Locations\LocationAddress;
use Daika7ana\Innoship\DTOs\Locations\LocationFilter;
use Daika7ana\Innoship\DTOs\Locations\UpdateLocation;
use Daika7ana\Innoship\DTOs\Locations\UpdateLocationCourierService;
use Daika7ana\Innoship\Tests\Support\ResponseFixtureFactory;
use Daika7ana\Innoship\Tests\Support\TestClientFactory;
use PHPUnit\Framework\TestCase;

class LocationsResourceTest extends TestCase
{
    public function testListUsesGetPathAndCountryCodeQuery(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json([
                ['courierLocationId' => 5, 'name' => 'HQ', 'countryCode' => 'RO'],
            ]),
        );

        $result = $client->locations()->list('RO');

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/ClientLocation', $request->getUri()->getPath());
        $this->assertSame('HQ', $result[0]['name']);

        parse_str($request->getUri()->getQuery(), $query);
        $this->assertSame('RO', $query['countryCode']);
    }

    public function testListOmitsNullCountryCode(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json([]),
        );

        $client->locations()->list();

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertArrayNotHasKey('countryCode', $query);
    }

    public function testGetCreateUpdateDeletePathsAndMethods(): void
    {
        $methods = [];

        [$client, $httpClient] = TestClientFactory::create(
            static function ($request) use (&$methods): \GuzzleHttp\Psr7\Response {
                $methods[] = [$request->getMethod(), $request->getUri()->getPath()];

                return match ($request->getMethod()) {
                    'GET' => ResponseFixtureFactory::json(['ok' => true]),
                    'POST' => new \GuzzleHttp\Psr7\Response(200, [], '35773'),
                    default => new \GuzzleHttp\Psr7\Response(200, [], 'true'),
                };
            },
        );

        $location = new UpdateLocation(id: 1, name: 'HQ');

        $this->assertSame(['ok' => true], $client->locations()->get(9));
        $this->assertSame(35773, $client->locations()->create($location));
        $this->assertTrue($client->locations()->update(9, $location));
        $this->assertTrue($client->locations()->delete(9));

        $this->assertSame([
            ['GET', '/api/ClientLocation/9'],
            ['POST', '/api/ClientLocation'],
            ['PUT', '/api/ClientLocation/9'],
            ['DELETE', '/api/ClientLocation/9'],
        ], $methods);
    }

    public function testSummaryAndValidateSendBody(): void
    {
        $bodies = [];

        [$client] = TestClientFactory::create(
            static function ($request) use (&$bodies): \GuzzleHttp\Psr7\Response {
                $bodies[] = json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR);

                return ResponseFixtureFactory::json([]);
            },
        );

        $client->locations()->summary(new LocationFilter(name: ['HQ'], take: 20));
        $client->locations()->validate(new UpdateLocation(name: 'HQ'));

        $this->assertSame(['name' => ['HQ'], 'take' => 20], $bodies[0]);
        $this->assertSame(['name' => 'HQ'], $bodies[1]);
    }

    public function testAddCourierSendsExpectedBody(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json([]),
        );

        $client->locations()->addCourier(4, 2, true);

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/ClientLocation/4/Courier', $request->getUri()->getPath());
        $this->assertSame(
            ['courierId' => 2, 'isEmagMarketPlace' => true],
            json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR),
        );
    }

    public function testRemoveCourierSerializesFalseQueryFlags(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json([]),
        );

        $client->locations()->removeCourier(4, 2);

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('DELETE', $request->getMethod());
        $this->assertSame('/api/ClientLocation/4/Courier/2', $request->getUri()->getPath());

        parse_str($request->getUri()->getQuery(), $query);
        $this->assertSame('false', $query['force']);
        $this->assertSame('false', $query['isEmagMarketPlace']);
    }

    public function testUpdateCourierServicesUsesPutAndSendsListBody(): void
    {
        $bodies = [];
        $methods = [];

        [$client] = TestClientFactory::create(
            static function ($request) use (&$bodies, &$methods): \GuzzleHttp\Psr7\Response {
                $methods[] = $request->getMethod();
                $bodies[] = json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR);

                return ResponseFixtureFactory::json([]);
            },
        );

        $services = [
            new UpdateLocationCourierService(courierId: 2, serviceId: 5, enabled: true),
            new UpdateLocationCourierService(courierId: 3, serviceId: 6),
        ];

        $client->locations()->updateCourierServices(4, $services);
        $client->locations()->updateCourierServices(4, $services, true, true);

        $this->assertSame(['PUT', 'PATCH'], $methods);
        $this->assertSame([
            ['courierId' => 2, 'serviceId' => 5, 'enabled' => true],
            ['courierId' => 3, 'serviceId' => 6],
        ], $bodies[0]);
        $this->assertSame($bodies[0], $bodies[1]);
    }

    public function testUpdateCourierServicesApplyToAllQuery(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json([]),
        );

        $client->locations()->updateCourierServices(4, [], true);

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertSame('true', $query['applyToAllLocations']);
    }

    public function testUploadLogoPostsAndUnwrapsValue(): void
    {
        $methods = [];
        [$client, $httpClient] = TestClientFactory::create(
            static function ($request) use (&$methods): \GuzzleHttp\Psr7\Response {
                $methods[] = $request->getMethod();

                return ResponseFixtureFactory::json([
                    'value' => ['logoUrl' => 'https://cdn/logo.png', 'success' => true],
                    'isSuccess' => true,
                    'errors' => [],
                ]);
            },
        );

        $response = $client->locations()->uploadLogo(4, 'Zm9v', false);

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/ClientLocation/4/Logo', $request->getUri()->getPath());
        $this->assertSame(['base64Logo' => 'Zm9v'], json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR));

        $this->assertSame('https://cdn/logo.png', $response->logoUrl);
        $this->assertTrue($response->success);
        $this->assertTrue($response->isSuccess);
        $this->assertSame([], $response->errors->all());

        $response = $client->locations()->uploadLogo(4, 'Zm9v', true);
        $this->assertSame(['POST', 'PUT'], $methods);
        $this->assertNotNull($response);
    }

    public function testDeleteLogoUsesDeleteAndUnwrapsValue(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json([
                'value' => ['logoUrl' => null, 'success' => true],
                'isSuccess' => true,
                'errors' => ['warning'],
            ]),
        );

        $response = $client->locations()->deleteLogo(4);

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('DELETE', $request->getMethod());
        $this->assertSame('/api/ClientLocation/4/Logo', $request->getUri()->getPath());
        $this->assertTrue($response->success);
        $this->assertSame(['warning'], $response->errors->all());
    }

    public function testUpdateSerializesNestedAddress(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json([]),
        );

        $client->locations()->update(9, new UpdateLocation(
            address: new LocationAddress(name: 'HQ', localityId: 12, countyId: 3, countryCode: 'RO'),
            id: 9,
            name: 'Headquarters',
        ));

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame(
            [
                'address' => [
                    'name' => 'HQ',
                    'localityId' => 12,
                    'countyId' => 3,
                    'countryCode' => 'RO',
                    'ignoreLocalityId' => false,
                ],
                'id' => 9,
                'name' => 'Headquarters',
            ],
            json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR),
        );
    }
}
