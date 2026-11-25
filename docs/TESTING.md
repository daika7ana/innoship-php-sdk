# Testing

## Commands

```bash
composer ci         # validate --strict -> pint --test -> phpstan -> phpunit tests/Unit
composer pint       # style check only
composer stan       # PHPStan level 8 over src/
composer test       # full suite (smoke tests skip without INNOSHIP_API_KEY)
composer test:unit  # unit tests only
composer test:smoke # live smoke suite; requires INNOSHIP_API_KEY
vendor/bin/pint     # actually fix style
```

Run a single file or filter:

```bash
composer test -- tests/Unit/Resources/OrdersResourceTest.php
composer test -- --filter=OrdersResourceTest
```

## How the tests work

- `tests/Support/TestClientFactory::create($handler)` returns
  `[Client, FakeHttpClient]`: the client is wired to the fake transport, which
  sends each request through the handler.
- The handler receives the real PSR-7 request and returns a real PSR-7 response.
- `tests/Support/ResponseFixtureFactory` holds canned JSON payloads.
- Unit tests never touch the network, so no credentials are required.
- `tests/Smoke` holds live checks that `markTestSkipped()` unless
  `INNOSHIP_API_KEY` is set; they are not part of `composer ci`.

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
`composer test:unit` on 8.3, 8.4, and 8.5. Smoke tests are not run in CI.
