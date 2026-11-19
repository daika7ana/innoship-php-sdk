<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Resources;

use Daika7ana\Innoship\Client;
use Daika7ana\Innoship\DTOs\OfflineOrders\ActivateOrderRequest;
use Daika7ana\Innoship\DTOs\OfflineOrders\EditOrderRequest;
use Daika7ana\Innoship\DTOs\OfflineOrders\OfflineOrderAddRequest;
use Daika7ana\Innoship\Exceptions\AuthenticationException;
use Daika7ana\Innoship\Exceptions\TransportException;
use Daika7ana\Innoship\Exceptions\UnexpectedStatusException;
use Daika7ana\Innoship\Exceptions\ValidationException;
use Daika7ana\Innoship\Support\ApiResponseMapper;

class OfflineOrdersResource
{
    public function __construct(
        private Client $client,
    ) {}

    /**
     * Add offline orders.
     *
     * @param list<OfflineOrderAddRequest> $orders
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     * @return array<string, mixed>
     */
    public function add(array $orders): array
    {
        return $this->send('POST', '/api/OfflineOrder/Add', array_map(
            static fn(OfflineOrderAddRequest $order): array => $order->toArray(),
            $orders,
        ));
    }

    /**
     * Edit offline orders.
     *
     * @param list<EditOrderRequest> $orders
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     * @return array<string, mixed>
     */
    public function edit(array $orders): array
    {
        return $this->send('PUT', '/api/OfflineOrder/Edit', array_map(
            static fn(EditOrderRequest $order): array => $order->toArray(),
            $orders,
        ));
    }

    /**
     * Activate offline orders.
     *
     * @param list<ActivateOrderRequest> $orders
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     * @return array<string, mixed>
     */
    public function activate(array $orders): array
    {
        return $this->send('PUT', '/api/OfflineOrder/Activate', array_map(
            static fn(ActivateOrderRequest $order): array => $order->toArray(),
            $orders,
        ));
    }

    /**
     * @param list<array<array-key, mixed>> $body
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     * @return array<string, mixed>
     */
    private function send(string $method, string $path, array $body): array
    {
        return ApiResponseMapper::decodeOptionalJson(
            $this->client->request($method, $path, $body),
            200,
        );
    }
}
