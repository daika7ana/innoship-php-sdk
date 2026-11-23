<div align="center">

<h1>Innoship PHP SDK</h1>

[![CI](https://github.com/daika7ana/innoship-php-sdk/actions/workflows/ci.yml/badge.svg)](https://github.com/daika7ana/innoship-php-sdk/actions/workflows/ci.yml)
[![PHP 8.3+](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=flat-square&logo=php)](https://www.php.net/)
[![PHPUnit](https://img.shields.io/badge/PHPUnit-12%E2%80%9313-0A7BBB?style=flat-square)](docs/TESTING.md)
[![PSR Standards](https://img.shields.io/badge/PSR-7%2F17%2F18-blue?style=flat-square)](https://www.php-fig.org/)
[![License](https://img.shields.io/badge/license-GPL--3.0--or--later-green?style=flat-square)](LICENSE)

A modern, type-safe PHP SDK for the **Innoship shipping API**

</div>

> 🚀 Framework-agnostic, API-key powered, fully typed with DTOs, production-ready, and built for multi-courier shipping.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
- [Authentication](#authentication)
- [Supported Resources](#supported-resources)
- [Framework Support](#framework-support)
- [Testing](#testing)
- [Documentation](#documentation)

## Features

### Core Strengths

- ✅ **API key authentication** — send `X-Api-Key` with every request, no OAuth dance
- ✅ **Explicit API versioning** — `api-version` travels as both a header and a query parameter
- ✅ **PSR-18 HTTP client** — pluggable; the bundled Guzzle adapter is used by default
- ✅ **Fully typed DTOs** — request payloads serialize from readonly objects; responses map back to typed objects
- ✅ **Enums for every documented enum** — couriers, parcel types, payment types, label formats, incoterms, and more
- ✅ **Complete API coverage** — orders, prices, labels, manifests, pickups, locations, notifications, offline orders, vouchers, repository
- ✅ **Strict quality gates** — PHPStan level 8, Laravel Pint, PHPUnit
- ✅ **Framework-agnostic** — works in Laravel, Symfony, or plain PHP

### Perfect For

- 🎯 Laravel, Symfony, or plain PHP applications
- 🎯 Multi-courier shipping workflows — orders, carrier price rates, labels, and pickups through one client
- 🎯 Teams that prefer typed request and response DTOs over raw arrays
- 🎯 Projects that enforce strict static analysis and test gates

## Requirements

- **PHP 8.3+**
- `ext-json`
- PSR-7 / PSR-17 / PSR-18 compatible environment

## Installation

### Via Composer

```bash
composer require daika7ana/innoship-php-sdk
```

## Quick Start

Creating an order takes a single request DTO:

```php
use Daika7ana\Innoship\Client;
use Daika7ana\Innoship\Config\ClientConfig;
use Daika7ana\Innoship\DTOs\Orders\OrderContent;
use Daika7ana\Innoship\DTOs\Orders\OrderParcel;
use Daika7ana\Innoship\DTOs\Orders\OrderParcelSize;
use Daika7ana\Innoship\DTOs\Orders\OrderRequest;
use Daika7ana\Innoship\DTOs\Orders\OrderRequestAddress;
use Daika7ana\Innoship\Enums\ParcelType;
use Daika7ana\Innoship\Enums\PaymentType;

$client = Client::create(config: new ClientConfig(apiKey: 'your-api-key'));

$today = (new DateTimeImmutable('today'))->format('Y-m-d');

$request = new OrderRequest(
    serviceId: 1,
    shipmentDate: $today,
    shipmentDateEnd: $today,
    externalClientLocation: 'RO',
    addressFrom: new OrderRequestAddress(
        name: 'Warehouse SRL',
        contactPerson: 'Warehouse SRL',
        country: 'RO',
        countyId: 10,
        localityId: 3502,
        addressText: 'Str. Depozitului 5',
        postalCode: '010864',
        phone: '+40734123456',
        email: 'warehouse@example.com',
    ),
    addressTo: new OrderRequestAddress(
        name: 'Jane Doe',
        contactPerson: 'Jane Doe',
        country: 'RO',
        countyId: 10,
        localityId: 3502,
        addressText: 'Str. Exemplu 10',
        postalCode: '010101',
        phone: '+40734123456',
        email: 'jane@example.com',
    ),
    payment: PaymentType::Sender,
    content: new OrderContent(
        parcelsCount: 1,
        totalWeight: 1.0,
        contents: 'Books',
        parcels: [
            new OrderParcel(
                sequenceNo: 1,
                weight: 1.0,
                type: ParcelType::Parcel,
                size: new OrderParcelSize(width: 20.0, height: 10.0, length: 30.0),
            ),
        ],
    ),
);

$order = $client->orders()->create($request);

echo $order->courierShipmentId;
echo $order->price?->totalAmount;
```

Get a price without creating an order:

```php
$prices = $client->prices()->getPrices($request);

foreach ($prices->rates as $rate) {
    echo $rate->carrier . ': ' . $rate->rateTotalAmount . ' ' . $rate->rateCurrency;
}
```

Download a label as a PDF:

```php
$label = $client->labels()->downloadLabel(
    courierId: $order->courier,
    awb: (string) $order->courierShipmentId,
);

file_put_contents('label.pdf', $label->contents());
```

`getLabel()` returns the same label as base64 JSON (`$label->contents`) when you
need it inline rather than as a file.

That's it! You've created an order, priced a shipment, and downloaded a label.

## Configuration

### Base URL & API Version

The default base URL is `https://api.innoship.com`. Override it for a proxy or a
test gateway:

```php
use Daika7ana\Innoship\Config\ClientConfig;

$config = new ClientConfig(
    baseUrl: 'https://api.innoship.com',
    apiKey: 'your-api-key',
    apiVersion: '1.0',
    acceptLanguage: 'en',
);
```

`apiVersion` is sent both as the `api-version` header and query parameter, as
required by the API.

### Environment Variables

Pass the key explicitly or let the client read `INNOSHIP_API_KEY`:

```php
$client = Client::create(); // reads INNOSHIP_API_KEY

$client = Client::create(config: new ClientConfig(apiKey: 'your-api-key'));

$client = Client::create()->setApiKey('rotated-key');
```

Requests throw `AuthenticationException` when the key is empty.

### Request Timeouts

`ClientConfig::timeout` is `null` by default. Precedence: the per-call argument → `ClientConfig::timeout` → the transport's own default (`GuzzleHttpClient` uses 30 s to respond and 10 s to connect unless you pass `timeout:`/`connectTimeout:`; an injected `GuzzleClient` keeps its own configuration). A bare PSR-18 client keeps its own timeout and cannot receive the per-call argument — passing one throws `InvalidArgumentException`; combining a bare client with a non-null `ClientConfig::timeout` also throws.

Set a different SDK default with `ClientConfig(timeout: ...)` or override per call:

```php
$client->request('GET', '/api/ClientLocation', null, [], 2.5);
```

For a different budget, `$slow = $client->withTimeout(30.0);` returns a new
client sharing the transport; the original keeps its timeout.

## Authentication

### X-Api-Key

Innoship uses a single API key header:

```http
X-Api-Key: your-api-key
```

The SDK adds it to every request. `401` responses and a missing key both raise
`Daika7ana\Innoship\Exceptions\AuthenticationException`.

`apiVersion` (default `1.0`) is sent alongside it as both the `api-version`
header and a query parameter.

## Supported Resources

| Resource | Methods |
|----------|---------|
| `orders()` | `create`, `simulate`, `validate`, `simulateMultiple`, `importFile`, `delete`, `deleteParcel`, `updateStatus`, `updateParcels`, `updateCustomAttributes`, `getReturnAwbInfo`, `uploadDocuments`, `downloadDocument` |
| `prices()` | `getPrices` |
| `labels()` | `getLabel`, `downloadLabel`, `getVoucherLabel`, `getParcelLabel`, `createBulk`, `getBulk`, `getCustomLabel`, `getCustomParcelLabel` |
| `manifests()` | `create`, `get` |
| `couriers()` | `requestPickup`, `generateLocationId`, `listLocations`, `setLocationId` |
| `locations()` | `list`, `get`, `create`, `update`, `delete`, `summary`, `validate`, `addCourier`, `removeCourier`, `updateCourierServices`, `uploadLogo`, `deleteLogo` |
| `notifications()` | `sendSms`, `getByAwb`, `getByTemplate` |
| `offlineOrders()` | `add`, `edit`, `activate` |
| `vouchers()` | `create`, `activate` |
| `repository()` | `add` |

See [docs/RESOURCES.md](docs/RESOURCES.md) for the endpoint map, parameters, and
examples.

### Scope Note

The SDK covers the endpoints published in the official Innoship Swagger
(`https://api.innoship.com/swagger/v1/swagger.json`). Innoship's support docs
also mention read-only endpoints on `https://query.innoship.com` (order
retrieval, tracking, location lookup); those are not part of that Swagger and
are therefore not wrapped.

## Framework Support

### Zero Framework Dependencies

The package carries no framework in `require` and works the same everywhere:

- 🌐 **Laravel** applications
- 🌐 **Symfony** projects
- 🌐 **Plain PHP** applications
- 🌐 Any **PHP 8.3+** environment

### Custom HTTP Client

The SDK uses its Guzzle adapter by default. To supply your own transport,
implement `Daika7ana\Innoship\Http\HttpClientInterface`:

```php
use Daika7ana\Innoship\Client;
use Daika7ana\Innoship\Http\HttpClientInterface;

/** @var HttpClientInterface $customClient */
$client = Client::create(httpClient: $customClient);
```

A plain PSR-18 client is wrapped automatically, so
`Client::create(httpClient: $psr18Client)` works. Such a client keeps its own
timeout: leaving `ClientConfig::timeout` at `null` is required, otherwise the
constructor throws `InvalidArgumentException`. Passing it together with a
non-null `ClientConfig::timeout` throws as well. Implement `HttpClientInterface`
instead if you need the SDK's per-request timeout.

Both forms use the bundled PSR-17 factories by default; you may also pass
`requestFactory:` and `streamFactory:` explicitly to override them.

`HttpClientInterface::sendRequest()` accepts an optional timeout, so custom
clients can honour per-request timeouts.

## Testing

The unit suite is offline: tests run against a fake PSR-18 client and the JSON
samples in [`tests/Fixtures/`](tests/Fixtures). The optional smoke suite in
[`tests/Smoke/`](tests/Smoke) calls the live API.

### Run Static Analysis

```bash
composer stan       # PHPStan level 8 over src/
```

### Run Tests

```bash
composer test       # full PHPUnit suite (smoke tests skip without a key)
composer test:unit  # unit tests only
composer test:smoke # live smoke suite; requires INNOSHIP_API_KEY
```

### Run The Same Checks As CI

```bash
composer ci         # validate -> pint -> stan -> test:unit
```

### Test Environment Variables

The unit suite reads none. `composer test:smoke` needs `INNOSHIP_API_KEY`;
without it the smoke tests skip and the run still passes. Smoke tests are not
part of `composer ci`.

## Documentation

| Guide | Purpose |
|-------|---------|
| 📖 [Quickstart](docs/QUICKSTART.md) | First order, price, and label |
| 🔧 [Installation](docs/INSTALLATION.md) | Setup details |
| ⚙️ [Configuration](docs/CONFIGURATION.md) | Base URL, API key, timeouts |
| 🔐 [Authentication](docs/AUTHENTICATION.md) | API key handling |
| 🔗 [Resources](docs/RESOURCES.md) | Complete endpoint reference |
| 📦 [DTOs](docs/DTOS.md) | Request/response DTOs and enums |
| ✅ [Testing](docs/TESTING.md) | Running the suite |
| ❌ [Errors](docs/ERRORS.md) | Exception handling |
| 📚 [All Docs](docs/USAGE.md) | Documentation index |

---

<div align="center">

Made with ❤️

**Questions?** [Check the docs](docs/USAGE.md) or [open an issue](../../issues)

</div>
