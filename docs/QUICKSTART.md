# Quickstart

## 1. Install and configure

```bash
composer require daika7ana/innoship-php-sdk
```

```php
use Daika7ana\Innoship\Client;
use Daika7ana\Innoship\Config\ClientConfig;

$client = Client::create(config: new ClientConfig(apiKey: 'your-api-key'));
```

`Client::create()` without arguments reads `INNOSHIP_API_KEY` from the environment.

## 2. Create an order

`serviceId` selects the Innoship service your account offers; read
`$rate->serviceId` / `$rate->service` from `getPrices()` to discover the valid
values. Leave `courierId` null to let Innoship choose the courier.

```php
use Daika7ana\Innoship\DTOs\Orders\OrderContent;
use Daika7ana\Innoship\DTOs\Orders\OrderParcel;
use Daika7ana\Innoship\DTOs\Orders\OrderParcelSize;
use Daika7ana\Innoship\DTOs\Orders\OrderRequest;
use Daika7ana\Innoship\DTOs\Orders\OrderRequestAddress;
use Daika7ana\Innoship\Enums\ParcelType;
use Daika7ana\Innoship\Enums\PaymentType;

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

echo $order->clientOrderId;
echo $order->courierShipmentId;
echo $order->trackingNumber;
```

## 3. Price before shipping

```php
$prices = $client->prices()->getPrices($request);

$cheapest = $prices->rates->first();

echo $cheapest?->carrier, ' ', $cheapest?->rateTotalAmount;
```

## 4. Print the label

```php
$label = $client->labels()->downloadLabel(
    courierId: $order->courier,
    awb: (string) $order->courierShipmentId,
);

file_put_contents('label.pdf', $label->contents());
```

`getLabel()` returns the label as base64 JSON instead, if you need it in memory.

## 5. Validate and simulate

Create an order without committing it:

```php
$simulation = $client->orders()->simulate($request);
```

Validate a payload (returns `true` when the order passes validation):

```php
$validation = $client->orders()->validate($request);
```

## Error handling

```php
use Daika7ana\Innoship\Exceptions\AuthenticationException;
use Daika7ana\Innoship\Exceptions\ValidationException;

try {
    $client->orders()->create($request);
} catch (ValidationException $e) {
    echo $e->getMessage();          // first API error + details
    echo $e->correlationId ?? '';
    foreach ($e->errors as $error) {
        echo $error->message;
    }
} catch (AuthenticationException $e) {
    // missing or rejected API key
}
```

Next: [Resources](RESOURCES.md) for the full endpoint map.
