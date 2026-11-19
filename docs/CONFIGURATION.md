# Configuration

`Daika7ana\Innoship\Config\ClientConfig` is a plain value object with public
properties. `withApiKey()` returns a modified copy.

| Option | Default | Purpose |
|--------|---------|---------|
| `baseUrl` | `https://api.innoship.com` | API origin |
| `apiKey` | `''` | Value of `X-Api-Key` |
| `apiVersion` | `1.0` | Sent as `api-version` header and query parameter |
| `acceptLanguage` | `en` | `Accept-Language` header |
| `timeout` | `30.0` | Default request timeout in seconds |

## Construct a client

```php
use Daika7ana\Innoship\Client;
use Daika7ana\Innoship\Config\ClientConfig;

$client = Client::create(config: new ClientConfig(
    baseUrl: 'https://api.innoship.com',
    apiKey: 'your-api-key',
    apiVersion: '1.0',
    timeout: 30.0,
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

`timeout` defaults to 30 seconds and applies to every request made by the
client; the connection phase is capped at 10 seconds. Override it per call:

```php
$client->request('POST', '/api/Order', new \Daika7ana\Innoship\DTOs\Orders\OrderRequest(), [], 5.0);
```

## Custom HTTP client

```php
use Daika7ana\Innoship\Client;
use Daika7ana\Innoship\Http\HttpClientInterface;

/** @var HttpClientInterface $httpClient */
$client = Client::create(httpClient: $httpClient);
```

`HttpClientInterface::sendRequest()` receives the optional timeout argument.
