<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Tests\Unit\Http;

use Daika7ana\Innoship\Exceptions\TransportException;
use Daika7ana\Innoship\Http\GuzzleHttpClient;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class GuzzleHttpClientTest extends TestCase
{
    /**
     * @param array<int, mixed> $queue
     * @param array<string, mixed>|null $captured
     */
    private function createClient(array $queue, ?array &$captured): GuzzleHttpClient
    {
        $stack = HandlerStack::create(new MockHandler($queue));

        $stack->push(static function (callable $handler) use (&$captured): callable {
            return static function ($request, array $options) use (&$captured, $handler) {
                $captured = $options;

                return $handler($request, $options);
            };
        });

        return new GuzzleHttpClient(new GuzzleClient(['handler' => $stack]));
    }

    public function testSendRequestForwardsTimeoutAndFlags(): void
    {
        $captured = null;
        $client = $this->createClient([new Response(200, [], 'ok')], $captured);

        $response = $client->sendRequest(new Request('GET', 'https://example.test/foo'), 5.0);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(5.0, $captured['timeout']);
        $this->assertSame(5.0, $captured['connect_timeout']);
        $this->assertFalse($captured['http_errors']);
        $this->assertFalse($captured['allow_redirects']);
    }

    public function testSendRequestWithoutTimeoutOmitsTimeoutOption(): void
    {
        $captured = null;
        $client = $this->createClient([new Response(200)], $captured);

        $client->sendRequest(new Request('GET', 'https://example.test/foo'), null);

        $this->assertIsArray($captured);
        $this->assertArrayNotHasKey('timeout', $captured);
    }

    public function testConnectExceptionIsWrappedInTransportException(): void
    {
        $exception = new ConnectException('boom', new Request('GET', 'https://example.test'));

        $captured = null;
        $client = $this->createClient([$exception], $captured);

        try {
            $client->sendRequest(new Request('GET', 'https://example.test/foo'), 5.0);
            $this->fail('Expected TransportException to be thrown.');
        } catch (TransportException $thrown) {
            $this->assertSame($exception, $thrown->getPrevious());
        }
    }
}
