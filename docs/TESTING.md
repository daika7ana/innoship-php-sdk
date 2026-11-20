# Testing

## Commands

```bash
composer ci        # validate --strict -> pint --test -> phpstan -> phpunit tests/Unit
composer pint      # style check only
composer stan      # PHPStan level 8 over src/
composer test      # full suite
composer test:unit # unit tests only
vendor/bin/pint    # actually fix style
```

Run a single file or filter:

```bash
composer test -- tests/Unit/Resources/OrdersResourceTest.php
composer test -- --filter=OrdersResourceTest
```

## How the tests work

- `tests/Support/TestClientFactory::create($handler)` returns a `Client` wired
  to `FakeHttpClient` plus `GuzzleHttp\Psr7\HttpFactory`.
- The handler receives the real PSR-7 request and returns a real PSR-7 response.
- `tests/Support/ResponseFixtureFactory` holds canned JSON payloads.
- No test touches the network, so no credentials are required.

```php
$request = new \Daika7ana\Innoship\DTOs\Orders\OrderRequest();

[$client, $httpClient] = TestClientFactory::create(
    static fn() => ResponseFixtureFactory::json(ResponseFixtureFactory::orderCreated(), 201),
);

$order = $client->orders()->create($request);

$this->assertSame('877551879', $order->courierShipmentId);
$this->assertSame('/api/Order', $httpClient->lastRequest?->getUri()->getPath());
```

## CI

`.github/workflows/ci.yml` runs the same commands as `composer ci`, split into
separate steps: `composer validate --strict`, Pint and PHPStan on PHP 8.3, and
PHPUnit on 8.3, 8.4, and 8.5.
