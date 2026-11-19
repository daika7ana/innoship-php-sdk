<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Http;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface HttpClientInterface extends ClientInterface
{
    /**
     * @param float|null $timeout Optional request timeout in seconds. Null uses the client default.
     *
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function sendRequest(RequestInterface $request, ?float $timeout = null): ResponseInterface;
}
