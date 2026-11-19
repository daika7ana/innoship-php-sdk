# Installation

## Requirements

- PHP 8.3 or newer
- `ext-json`
- Composer 2

The SDK declares PSR-7/17/18 interfaces and ships a Guzzle adapter. To use a
different transport, implement `Daika7ana\Innoship\Http\HttpClientInterface`; a
plain PSR-18 client needs a small adapter because that interface adds the
optional `?float $timeout` argument.

## Install

```bash
composer require daika7ana/innoship-php-sdk
```

## Verify

```php
require 'vendor/autoload.php';

use Daika7ana\Innoship\Config\ClientConfig;

$config = new ClientConfig(apiKey: 'your-api-key');

echo $config->baseUrl; // https://api.innoship.com
```

## Development install

```bash
git clone https://github.com/daika7ana/innoship-php-sdk.git
cd innoship-php-sdk
composer install
composer ci
```
