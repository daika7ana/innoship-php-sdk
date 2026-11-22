<?php

declare(strict_types=1);

namespace Daika7ana\Innoship;

use Daika7ana\Innoship\Config\ClientConfig;
use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Exceptions\AuthenticationException;
use Daika7ana\Innoship\Exceptions\TransportException;
use Daika7ana\Innoship\Http\GuzzleHttpClient;
use Daika7ana\Innoship\Http\HttpClientInterface;
use Daika7ana\Innoship\Http\Psr18HttpClientAdapter;
use Daika7ana\Innoship\Resources\CouriersResource;
use Daika7ana\Innoship\Resources\LabelsResource;
use Daika7ana\Innoship\Resources\LocationsResource;
use Daika7ana\Innoship\Resources\ManifestsResource;
use Daika7ana\Innoship\Resources\NotificationsResource;
use Daika7ana\Innoship\Resources\OfflineOrdersResource;
use Daika7ana\Innoship\Resources\OrdersResource;
use Daika7ana\Innoship\Resources\PricesResource;
use Daika7ana\Innoship\Resources\RepositoryResource;
use Daika7ana\Innoship\Resources\VouchersResource;
use Daika7ana\Innoship\Support\JsonHelper;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Client
{
    private HttpClientInterface $httpClient;

    private ClientConfig $config;

    private ?OrdersResource $ordersResource = null;

    private ?PricesResource $pricesResource = null;

    private ?LabelsResource $labelsResource = null;

    private ?ManifestsResource $manifestsResource = null;

    private ?CouriersResource $couriersResource = null;

    private ?LocationsResource $locationsResource = null;

    private ?NotificationsResource $notificationsResource = null;

    private ?OfflineOrdersResource $offlineOrdersResource = null;

    private ?VouchersResource $vouchersResource = null;

    private ?RepositoryResource $repositoryResource = null;

    public function __construct(
        ClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
        ?ClientConfig $config = null,
    ) {
        if ($config !== null && $config->timeout !== null && !$httpClient instanceof HttpClientInterface) {
            throw new \InvalidArgumentException('ClientConfig::timeout cannot be applied to a foreign PSR-18 client; configure the timeout on that client or leave it null.');
        }

        $this->httpClient = $httpClient instanceof HttpClientInterface
            ? $httpClient
            : new Psr18HttpClientAdapter($httpClient);
        $this->config = $config ?? ClientConfig::fromEnvironment();
    }

    public function getConfig(): ClientConfig
    {
        return $this->config;
    }

    /**
     * A client sharing this client's transport and PSR-17 factories, but with
     * a different request timeout budget.
     */
    public function withTimeout(?float $timeout): self
    {
        if ($timeout !== null && $this->httpClient instanceof Psr18HttpClientAdapter) {
            throw new \InvalidArgumentException('ClientConfig::timeout cannot be applied to a foreign PSR-18 client; configure the timeout on that client or leave it null.');
        }

        return new self(
            $this->httpClient,
            $this->requestFactory,
            $this->streamFactory,
            $this->config->withTimeout($timeout),
        );
    }

    public function setApiKey(string $apiKey): self
    {
        $this->config = $this->config->withApiKey($apiKey);

        return $this;
    }

    /**
     * @throws AuthenticationException
     */
    public function createRequest(string $method, string $path): RequestInterface
    {
        if ($this->config->apiKey === '') {
            throw new AuthenticationException(
                'Innoship API key is missing. Pass one to ClientConfig or set INNOSHIP_API_KEY.',
            );
        }

        $uri = rtrim($this->config->baseUrl, '/') . '/' . ltrim($path, '/');
        $request = $this->requestFactory->createRequest($method, $uri);

        $query = $request->getUri()->getQuery();
        $query .= ($query === '' ? '' : '&') . 'api-version=' . rawurlencode($this->config->apiVersion);

        $request = $request
            ->withUri($request->getUri()->withQuery($query))
            ->withHeader('Accept', 'application/json')
            ->withHeader('Accept-Language', $this->config->acceptLanguage)
            ->withHeader('api-version', $this->config->apiVersion)
            ->withHeader('X-Api-Key', $this->config->apiKey);

        return $request;
    }

    /**
     * Sends a request, optionally encoding an array or DTO as the JSON body.
     *
     * @param Arrayable|array<array-key, mixed>|null $body
     * @param array<string, mixed> $query
     *
     * @throws AuthenticationException
     * @throws \Daika7ana\Innoship\Exceptions\TransportException
     */
    public function request(
        string $method,
        string $path,
        Arrayable|array|null $body = null,
        array $query = [],
        ?float $timeout = null,
    ): ResponseInterface {
        $queryString = $this->buildQuery($query);

        if ($queryString !== '') {
            $path .= (str_contains($path, '?') ? '&' : '?') . $queryString;
        }

        $request = $this->createRequest($method, $path);

        if ($body !== null) {
            $payload = $body instanceof Arrayable ? $body->toArray() : $body;

            // An empty DTO must serialize as {} so object-typed endpoints accept it.
            if ($body instanceof Arrayable && $payload === []) {
                $payload = new \stdClass();
            }

            $request = $request->withHeader('Content-Type', 'application/json')
                ->withBody($this->streamFactory->createStream(JsonHelper::encode($payload)));
        }

        return $this->send($request, $timeout);
    }

    /**
     * @throws \Daika7ana\Innoship\Exceptions\TransportException
     */
    public function send(RequestInterface $request, ?float $timeout = null): ResponseInterface
    {
        try {
            return $this->httpClient->sendRequest($request, $timeout ?? $this->config->timeout);
        } catch (TransportException $e) {
            throw $e;
        } catch (ClientExceptionInterface $e) {
            throw new TransportException(
                message: $e->getMessage(),
                code: $e->getCode(),
                previous: $e,
            );
        }
    }

    public function streamFactory(): StreamFactoryInterface
    {
        return $this->streamFactory;
    }

    public function orders(): OrdersResource
    {
        return $this->ordersResource ??= new OrdersResource($this);
    }

    public function prices(): PricesResource
    {
        return $this->pricesResource ??= new PricesResource($this);
    }

    public function labels(): LabelsResource
    {
        return $this->labelsResource ??= new LabelsResource($this);
    }

    public function manifests(): ManifestsResource
    {
        return $this->manifestsResource ??= new ManifestsResource($this);
    }

    public function couriers(): CouriersResource
    {
        return $this->couriersResource ??= new CouriersResource($this);
    }

    public function locations(): LocationsResource
    {
        return $this->locationsResource ??= new LocationsResource($this);
    }

    public function notifications(): NotificationsResource
    {
        return $this->notificationsResource ??= new NotificationsResource($this);
    }

    public function offlineOrders(): OfflineOrdersResource
    {
        return $this->offlineOrdersResource ??= new OfflineOrdersResource($this);
    }

    public function vouchers(): VouchersResource
    {
        return $this->vouchersResource ??= new VouchersResource($this);
    }

    public function repository(): RepositoryResource
    {
        return $this->repositoryResource ??= new RepositoryResource($this);
    }

    public static function create(
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
        ?ClientConfig $config = null,
    ): self {
        $httpClient ??= new GuzzleHttpClient();
        $config ??= ClientConfig::fromEnvironment();

        if ($requestFactory === null || $streamFactory === null) {
            $psr17Factory = new \GuzzleHttp\Psr7\HttpFactory();
            $requestFactory ??= $psr17Factory;
            $streamFactory ??= $psr17Factory;
        }

        return new self($httpClient, $requestFactory, $streamFactory, $config);
    }

    /**
     * @param array<string, mixed> $query
     */
    private function buildQuery(array $query): string
    {
        $pairs = [];

        foreach ($query as $key => $value) {
            if ($value === null) {
                continue;
            }

            foreach (is_array($value) ? $value : [$value] as $item) {
                if ($item === null) {
                    continue;
                }

                if (is_bool($item)) {
                    $item = $item ? 'true' : 'false';
                }

                $pairs[] = rawurlencode((string) $key) . '=' . rawurlencode(is_scalar($item) ? (string) $item : '');
            }
        }

        return implode('&', $pairs);
    }
}
