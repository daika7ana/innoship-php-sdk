# Configuration

`Daika7ana\Innoship\Config\ClientConfig` is a plain value object with public
properties. `withApiKey()` returns a modified copy.

| Option | Default | Purpose |
|--------|---------|---------|
| `baseUrl` | `https://api.innoship.com` | API origin |
| `apiKey` | `''` | Value of `X-Api-Key` |
| `apiVersion` | `1.0` | Sent as `api-version` header and query parameter |
| `acceptLanguage` | `en` | `Accept-Language` header |
| `timeout` | `null` | Request timeout in seconds; `null` uses the transport default |

## Construct a client

```php
use Daika7ana\Innoship\Client;
use Daika7ana\Innoship\Config\ClientConfig;

$client = Client::create(config: new ClientConfig(
    baseUrl: 'https://api.innoship.com',
    apiKey: 'your-api-key',
    apiVersion: '1.0',
));
```

## Environment

`Client::create()` with no config calls `ClientConfig::fromEnvironment()`, which
reads `INNOSHIP_API_KEY`:

```bash
export INNOSHIP_API_KEY=your-api-key
```

```php
$client = Client::create();
```

## Change the key at runtime

```php
$client->setApiKey('rotated-key');
```

## Timeouts

`timeout` is `null` by default, so the bundled `GuzzleHttpClient` applies its
own 30 s request / 10 s connect defaults. Precedence: the per-call argument →
`ClientConfig::timeout` → the transport's own default. Set another default with
`new ClientConfig(timeout: 10.0)` or override per call:

```php
$client->request('POST', '/api/Order', new \Daika7ana\Innoship\DTOs\Orders\OrderRequest(), [], 5.0);
```

`timeout` applies to the bundled transport. A foreign PSR-18 client keeps its
own timeout; passing both it and a non-null `ClientConfig::timeout` throws
`InvalidArgumentException`.

When some calls need a different budget than the rest, derive a client:
`$other = $client->withTimeout(30.0);` returns a new client sharing the
transport and the PSR-17 factories, so no new transport is built and the API
key is carried over; the original client keeps its timeout. `withTimeout(null)`
returns to the transport default.

## Custom HTTP client

```php
use Daika7ana\Innoship\Client;
use Daika7ana\Innoship\Http\HttpClientInterface;

/** @var HttpClientInterface $httpClient */
$client = Client::create(httpClient: $httpClient);
```

A bare PSR-18 client is wrapped automatically; it keeps its own timeout (see
above), and passing it together with a non-null `ClientConfig::timeout` throws.
Passing PSR-17 `requestFactory:` and `streamFactory:` to `Client::create()` is
optional; the bundled factories are used otherwise.

`HttpClientInterface::sendRequest()` receives the optional timeout argument.
