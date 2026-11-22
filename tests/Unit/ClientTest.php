<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Tests\Unit;

use Daika7ana\Innoship\Client;
use Daika7ana\Innoship\Config\ClientConfig;
use Daika7ana\Innoship\DTOs\Orders\OrderRequest;
use Daika7ana\Innoship\DTOs\Orders\OrderRequestAddress;
use Daika7ana\Innoship\Exceptions\AuthenticationException;
use Daika7ana\Innoship\Exceptions\TransportException;
use Daika7ana\Innoship\Http\HttpClientInterface;
use Daika7ana\Innoship\Tests\Support\TestClientFactory;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ClientTest extends TestCase
{
    public function testCreateRequestAddsApiKeyAndVersion(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => new Response(200, [], '{}'),
            new ClientConfig(apiKey: 'secret-key', apiVersion: '1.0'),
        );

        $client->send($client->createRequest('GET', '/api/Order/ReturnAwbInfo/1/AWB'));

        $request = $httpClient->lastRequest;

        $this->assertNotNull($request);
        $this->assertSame('secret-key', $request->getHeaderLine('X-Api-Key'));
        $this->assertSame('1.0', $request->getHeaderLine('api-version'));
        $this->assertSame('application/json', $request->getHeaderLine('Accept'));
        $this->assertSame('https', $request->getUri()->getScheme());
        $this->assertSame('api.innoship.com', $request->getUri()->getHost());
        $this->assertSame('/api/Order/ReturnAwbInfo/1/AWB', $request->getUri()->getPath());
        $this->assertSame('api-version=1.0', $request->getUri()->getQuery());
    }

    public function testRequestEncodesArrayableBodyAsJson(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => new Response(200, [], '{}'),
        );

        $client->request('POST', '/api/Order/validate', new OrderRequestAddress(
            name: 'Sender SRL',
            country: 'RO',
            countyId: 5,
        ));

        $request = $httpClient->lastRequest;

        $this->assertNotNull($request);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('application/json', $request->getHeaderLine('Content-Type'));

        $body = json_decode((string) $request->getBody(), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame([
            'name' => 'Sender SRL',
            'country' => 'RO',
            'countyId' => 5,
        ], $body);
    }

    public function testRequestBuildsQueryWithBooleansAndLists(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => new Response(200, [], '{}'),
        );

        $client->request('GET', '/api/Label/bulk', null, [
            'useFile' => true,
            'skipCache' => false,
            'ids' => [1, 2],
            'ignored' => null,
        ]);

        $this->assertSame(
            'useFile=true&skipCache=false&ids=1&ids=2&api-version=1.0',
            $httpClient->lastRequest?->getUri()->getQuery(),
        );
    }

    public function testRequestThrowsWithoutApiKey(): void
    {
        [$client] = TestClientFactory::create(
            static fn() => new Response(200, [], '{}'),
            new ClientConfig(apiKey: ''),
        );

        $this->expectException(AuthenticationException::class);

        $client->request('GET', '/api/ClientLocation');
    }

    public function testSetApiKeyReplacesConfigKey(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => new Response(200, [], '{}'),
        );

        $client->setApiKey('rotated-key');
        $client->send($client->createRequest('GET', '/api/ClientLocation'));

        $this->assertSame('rotated-key', $httpClient->lastRequest?->getHeaderLine('X-Api-Key'));
        $this->assertSame('rotated-key', $client->getConfig()->apiKey);
    }

    public function testRequestPassesTimeout(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => new Response(200, [], '{}'),
            new ClientConfig(apiKey: 'key', timeout: 12.0),
        );

        $client->request('GET', '/api/ClientLocation');
        $this->assertSame(12.0, $httpClient->lastTimeout);

        $client->request('GET', '/api/ClientLocation', null, [], 2.5);
        $this->assertSame(2.5, $httpClient->lastTimeout);
    }

    public function testWithTimeoutDerivesAClientSharingTransport(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => new Response(200, [], '{}'),
            new ClientConfig(apiKey: 'test-api-key', timeout: 10.0),
        );

        $derived = $client->withTimeout(30.0);

        $this->assertSame(30.0, $derived->getConfig()->timeout);
        $this->assertSame(10.0, $client->getConfig()->timeout);
        $this->assertSame('test-api-key', $derived->getConfig()->apiKey);

        $derived->send($derived->createRequest('GET', '/api/ClientLocation'));
        $this->assertSame(30.0, $httpClient->lastTimeout);

        $client->send($client->createRequest('GET', '/api/ClientLocation'));
        $this->assertSame(10.0, $httpClient->lastTimeout);
    }

    public function testForeignPsr18ClientRejectsConfigTimeout(): void
    {
        $psr18Client = new class implements \Psr\Http\Client\ClientInterface {
            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                throw new \RuntimeException('Not expected to be called.');
            }
        };

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ClientConfig::timeout cannot be applied to a foreign PSR-18 client; configure the timeout on that client or leave it null.');

        Client::create(
            httpClient: $psr18Client,
            config: new ClientConfig(apiKey: 'test-api-key', timeout: 5.0),
        );
    }

    public function testResourcesAreCached(): void
    {
        [$client] = TestClientFactory::create(static fn() => new Response(200, [], '{}'));

        $this->assertSame($client->orders(), $client->orders());
        $this->assertSame($client->labels(), $client->labels());
        $this->assertSame($client->locations(), $client->locations());
    }

    public function testEmptyRequestDtoSerializesAsJsonObject(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => new Response(200, [], 'true'),
        );

        $client->orders()->validate(new OrderRequest());

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('{}', (string) $request->getBody());
    }

    public function testCustomHttpClientExceptionsAreWrapped(): void
    {
        $exception = new ConnectException('boom', new Request('GET', 'https://example.test'));

        $httpClient = new class ($exception) implements HttpClientInterface {
            public function __construct(private \Throwable $exception) {}

            public function sendRequest(RequestInterface $request, ?float $timeout = null): ResponseInterface
            {
                throw $this->exception;
            }
        };

        $factory = new HttpFactory();
        $client = new Client($httpClient, $factory, $factory, new ClientConfig(apiKey: 'key'));

        try {
            $client->orders()->validate(new OrderRequest());
            $this->fail('Expected TransportException to be thrown.');
        } catch (TransportException $thrown) {
            $this->assertSame($exception, $thrown->getPrevious());
        }
    }

    public function testWithTimeoutRejectsForeignPsr18ClientTransport(): void
    {
        $foreignClient = new class implements \Psr\Http\Client\ClientInterface {
            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                throw new \RuntimeException('Not expected to be called.');
            }
        };

        $client = Client::create(
            httpClient: $foreignClient,
            config: new ClientConfig(apiKey: 'test-api-key'),
        );

        $client->withTimeout(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ClientConfig::timeout cannot be applied to a foreign PSR-18 client; configure the timeout on that client or leave it null.');

        $client->withTimeout(30.0);
    }
}
