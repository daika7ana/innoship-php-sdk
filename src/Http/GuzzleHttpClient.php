<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Http;

use Daika7ana\Innoship\Exceptions\TransportException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class GuzzleHttpClient implements HttpClientInterface
{
    public function __construct(
        private GuzzleClient $guzzleClient = new GuzzleClient(),
    ) {}

    /**
     * @throws TransportException
     */
    public function sendRequest(RequestInterface $request, ?float $timeout = null): ResponseInterface
    {
        try {
            $options = [
                'http_errors' => false,
                'allow_redirects' => false,
            ];

            if ($timeout !== null) {
                $options['timeout'] = $timeout;
                $options['connect_timeout'] = min(10.0, $timeout);
            }

            return $this->guzzleClient->send($request, $options);
        } catch (GuzzleException $e) {
            throw new TransportException(
                message: $e->getMessage(),
                code: $e->getCode(),
                previous: $e,
            );
        }
    }
}
