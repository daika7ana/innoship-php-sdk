# Authentication

Innoship authenticates every request with an API key header:

```http
X-Api-Key: your-api-key
```

There is no token exchange, refresh flow, or password grant.

## Providing the key

```php
use Daika7ana\Innoship\Client;
use Daika7ana\Innoship\Config\ClientConfig;

$client = Client::create(config: new ClientConfig(apiKey: 'your-api-key'));
```

or via the environment:

```bash
export INNOSHIP_API_KEY=your-api-key
```

```php
$client = Client::create();
```

## Missing or rejected key

An empty key raises `Daika7ana\Innoship\Exceptions\AuthenticationException`
before the request is sent. A `401` response raises the same exception with the
API message:

```php
use Daika7ana\Innoship\Exceptions\AuthenticationException;

try {
    $client->locations()->list();
} catch (AuthenticationException $e) {
    echo $e->getMessage();
}
```

`403` responses are not authentication failures; they raise
`UnexpectedStatusException` with the API error message.
