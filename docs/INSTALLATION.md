# Installation

## Requirements

- PHP 8.3 or newer
- `ext-json`
- Composer 2

The SDK declares PSR-7/17/18 interfaces and ships a Guzzle adapter used by
default. Any PSR-18 client can be passed to `Client::create()`; the SDK wraps
it in its internal transport contract.

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
