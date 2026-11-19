<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Resources;

use Daika7ana\Innoship\Client;
use Daika7ana\Innoship\DTOs\Orders\OrderRequest;
use Daika7ana\Innoship\DTOs\Prices\PriceResponse;
use Daika7ana\Innoship\Exceptions\AuthenticationException;
use Daika7ana\Innoship\Exceptions\TransportException;
use Daika7ana\Innoship\Exceptions\UnexpectedStatusException;
use Daika7ana\Innoship\Exceptions\ValidationException;
use Daika7ana\Innoship\Support\ApiResponseMapper;

class PricesResource
{
    public function __construct(
        private Client $client,
    ) {}

    /**
     * Gets the prices for the specified order request.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function getPrices(OrderRequest $order): PriceResponse
    {
        $response = $this->client->request('POST', '/api/Price', $order);

        return PriceResponse::fromArray(ApiResponseMapper::decodeJson($response, [201, 200]));
    }
}
