# Resources

All paths are relative to `https://api.innoship.com`. The SDK appends
`api-version=1.0` to every request and sends `X-Api-Key`.

Every method throws `AuthenticationException`, `ValidationException`,
`UnexpectedStatusException`, or `TransportException`; these are omitted from the
signatures below.

Examples assume `$client = Client::create(...)` and `$request` is the
`OrderRequest` built in the [quickstart](QUICKSTART.md).

The manifests, couriers, client locations, notifications, offline orders,
vouchers, repository, and bulk-label examples below are derived from the DTO
definitions; they have not been exercised against the live API.

## Orders — `$client->orders()`

| Method | Endpoint |
|--------|----------|
| `create(OrderRequest $order): OrderResponse` | `POST /api/Order` |
| `simulate(OrderRequest $order): OrderResponse` | `POST /api/Order/simulate` |
| `validate(OrderRequest $order): bool` | `POST /api/Order/validate` |
| `simulateMultiple(array $orders): Collection<string, OrderSimulationResult>` | `POST /api/Order/multiple/simulate` |
| `importFile(string $fileContents, string $filename = 'orders.csv', array $userAllowedLocationIds = []): array` | `POST /api/Order/file` |
| `delete(int $courierId, string $awb): DeleteOrderResponse` | `DELETE /api/Order/{courierId}/awb/{awb}` |
| `deleteParcel(int $courierId, string $awb, string $parcelBarcode): CourierDeleteParcelsResponse` | `DELETE /api/Order/{courierId}/awb/{awb}/parcel/{parcelBarcode}` |
| `updateStatus(array $requests): array` | `POST /api/Order/UpdateStatus` |
| `updateParcels(UpdateParcelsRequest $request): OrderResponse` | `POST /api/Order/UpdateParcels` |
| `updateCustomAttributes(int $courierId, string $awb, array $attributes): array` | `POST /api/Order/UpdateCustomAttributes/{courierId}/awb/{awb}` |
| `getReturnAwbInfo(int $courierId, string $returnAwb): array` | `GET /api/Order/ReturnAwbInfo/{courierId}/{returnAwb}` |
| `uploadDocuments(UploadDocumentsRequest $request): UploadDocumentsResponse` | `POST /api/Order/UploadDocuments` |
| `downloadDocument(int $courierId, string $awb, ?DocumentType $document = null, ?FileType $type = null, ?FileFormat $format = null, bool $useFile = true): DocumentResponse` | `GET /api/Order/DownloadDocument/courier/{courierId}/awb/{awb}` |

`importFile` posts `multipart/form-data` with a `file` part. The published
Swagger omits this endpoint's body schema, so the part name follows the API
convention.

```php
$result = $client->orders()->importFile(
    fileContents: file_get_contents('orders.csv'),
    filename: 'orders.csv',
    userAllowedLocationIds: [12, 34],
);
```

### Update statuses

```php
use Daika7ana\Innoship\DTOs\Orders\UpdateStatusRequest;
use Daika7ana\Innoship\Enums\Courier;

$client->orders()->updateStatus([
    new UpdateStatusRequest(
        courierId: Courier::DPD,
        courierShipmentId: '877551879',
        statusId: 3,
        actionDateTime: '2026-11-19T10:00:00Z',
    ),
]);
```

## Prices — `$client->prices()`

| Method | Endpoint |
|--------|----------|
| `getPrices(OrderRequest $order): PriceResponse` | `POST /api/Price` |

```php
$prices = $client->prices()->getPrices($request);

foreach ($prices->rates as $rate) {
    echo $rate->carrier, ' ', $rate->rateTotalAmount, ' ', $rate->rateCurrency, PHP_EOL;
}
```

## Labels — `$client->labels()`

| Method | Endpoint |
|--------|----------|
| `getLabel(int $courierId, string $awb, string $type = 'PDF', string $format = 'A6', string $orientation = 'portrait', bool $includeVoucher = false, bool $skipCache = false, bool $noReprint = false): LabelResponse` | `GET /api/Label/by-courier/{courierId}/awb/{awb}` |
| `downloadLabel(int $courierId, string $awb, string $type = 'PDF', string $format = 'A6', string $orientation = 'portrait', bool $includeVoucher = false, bool $skipCache = false, bool $noReprint = false): FileResponse` | `GET /api/Label/by-courier/{courierId}/awb/{awb}` (`useFile=true`) |
| `getVoucherLabel(int $courierId, string $awb, string $type = 'PDF', string $format = 'A6', string $orientation = 'portrait', bool $skipCache = false, bool $noReprint = false): LabelResponse` | `GET /api/Label/by-courier/{courierId}/voucher/{awb}` |
| `getParcelLabel(int $courierId, string $awb, string $barcode, string $type = 'PDF', string $format = 'A6', string $orientation = 'portrait', bool $skipCache = false, bool $noReprint = false): LabelResponse` | `GET /api/Label/by-courier/{courierId}/awb/{awb}/parcel/{barcode}` |
| `createBulk(BulkLabelRequest $request): array` | `POST /api/Label/bulk` |
| `getBulk(string $operationId): LabelResponse` | `GET /api/Label/bulk/{operationId}` |
| `getCustomLabel(string $template, Courier\|string $courier, string $awb, ?LabelType $type = null, ?LabelFormat $format = null, bool $useFile = true): FileResponse` | `GET /api/CustomLabel/template/{template}/courier/{courier}/awb/{awb}` |
| `getCustomParcelLabel(string $template, Courier\|string $courier, string $awb, string $parcel, ?LabelType $type = null, ?LabelFormat $format = null, bool $useFile = true): FileResponse` | `GET /api/CustomLabel/template/{template}/courier/{courier}/awb/{awb}/parcel/{parcel}` |

`LabelResponse` carries `courier`, `contents` (base64), and `labels`.
A 200 body with neither `contents` nor `labels` throws
`UnexpectedStatusException` (`Innoship returned no label for this request.`)
instead of returning an empty response. An empty `labels` list is assumed to be
a valid bulk result, but this has not been exercised against the live API.
`FileResponse` exposes the raw `stream`, `contentType`, optional `filename`, and
a `contents(): string` helper for `downloadLabel()` and custom templates.

Bulk label generation is asynchronous: `getBulk()` throws
`ValidationException` while the operation is pending (the API's 400 message,
e.g. `Operation is pending`), so poll it until it returns a `LabelResponse`.

```php
use Daika7ana\Innoship\DTOs\Labels\BulkLabelItem;
use Daika7ana\Innoship\DTOs\Labels\BulkLabelRequest;
use Daika7ana\Innoship\Enums\Courier;
use Daika7ana\Innoship\Enums\LabelFormat;
use Daika7ana\Innoship\Enums\LabelType;

$operation = $client->labels()->createBulk(new BulkLabelRequest(
    labelType: LabelType::Pdf,
    labelFormat: LabelFormat::A4,
    awBs: [
        new BulkLabelItem(courierId: Courier::DPD, courierShipmentId: '877551879'),
        new BulkLabelItem(courierId: Courier::FanCourier, courierShipmentId: 'FAN-998877'),
    ],
));

// The Swagger declares no response schema; use the operation id from $operation.
$labels = $client->labels()->getBulk(operationId: 'operation-id-from-createBulk');
```

The list property is named `awBs`; each `BulkLabelItem::$courierId` is a
`Courier` enum, unlike the numeric courier id in the label path parameters.

## Manifests — `$client->manifests()`

| Method | Endpoint |
|--------|----------|
| `create(ManifestRequest $request): array` | `POST /api/Manifest` |
| `get(int $manifestId, ?ManifestLanguage $language = null): FileResponse` | `GET /api/Manifest` |

```php
use Daika7ana\Innoship\DTOs\Manifests\ManifestRequest;
use Daika7ana\Innoship\Enums\Courier;
use Daika7ana\Innoship\Enums\ManifestLanguage;

$manifest = $client->manifests()->create(new ManifestRequest(
    courierId: Courier::DPD,
    externalLocationId: 'LOC-1',
    lastPickHour: 17,
));

// The Swagger declares no response schema; pass the manifest id from $manifest.
$report = $client->manifests()->get(manifestId: 123, language: ManifestLanguage::Ro);
$pdf = $report->contents();
```

`lastPickHour` is an integer hour, not a time string; `courierId` is a
`Courier` enum in this request body.

## Couriers — `$client->couriers()`

| Method | Endpoint |
|--------|----------|
| `requestPickup(PickupRequest $request): PickupResponse` | `POST /api/Courier/RequestPickup` |
| `generateLocationId(int $locationId, int $courierId, ?GenerateCourierLocationIdRequest $request = null): GenerateCourierLocationIdResponse` | `POST /api/Courier/Location/GenerateId/{locationId}/{courierId}` |
| `listLocations(int $locationId, int $courierId): CourierLocationsResponse` | `GET /api/Courier/Location/List/{locationId}/{courierId}` |
| `setLocationId(int $locationId, int $courierId, int $courierLocationId): SetCourierLocationResponse` | `POST /api/Courier/Location/SetId/{locationId}/{courierId}/{courierLocationId}` |

```php
use Daika7ana\Innoship\DTOs\Couriers\CourierLocationAddress;
use Daika7ana\Innoship\DTOs\Couriers\GenerateCourierLocationIdRequest;
use Daika7ana\Innoship\DTOs\Couriers\PickupRequest;
use Daika7ana\Innoship\Enums\CallCourierPickupMode;

$pickup = $client->couriers()->requestPickup(new PickupRequest(
    courierId: 1,
    locationId: 12,
    pickupMode: CallCourierPickupMode::EndOfDay,
    lastPickupHour: 17,
    lastPickMinute: 30,
));

$generated = $client->couriers()->generateLocationId(
    locationId: 12,
    courierId: 1,
    request: new GenerateCourierLocationIdRequest(
        address: new CourierLocationAddress(
            name: 'Warehouse SRL',
            contactPerson: 'Warehouse SRL',
            phone: '+40734123456',
            countryCode: 'RO',
            localityName: 'Bucharest',
            addressText: 'Str. Depozitului 5',
            postalCode: '010864',
        ),
    ),
);
```

In this resource every `courierId` is the numeric courier id (as returned in
`$order->courier`), not the `Courier` enum. `PickupRequest::$courierId` is also
an int; `ManifestRequest` (under `manifests()`) takes the enum.

## Client locations — `$client->locations()`

| Method | Endpoint |
|--------|----------|
| `list(?string $countryCode = null): Collection` | `GET /api/ClientLocation` |
| `get(int $id): array` | `GET /api/ClientLocation/{id}` |
| `create(UpdateLocation $location): int` | `POST /api/ClientLocation` |
| `update(int $id, UpdateLocation $location): bool` | `PUT /api/ClientLocation/{id}` |
| `delete(int $id): bool` | `DELETE /api/ClientLocation/{id}` |
| `summary(LocationFilter $filter): array` | `POST /api/ClientLocation/Summary` |
| `validate(UpdateLocation $location): array` | `POST /api/ClientLocation/Validate` |
| `addCourier(int $locationId, int $courierId, bool $isEmagMarketPlace = false): array` | `POST /api/ClientLocation/{locationId}/Courier` |
| `removeCourier(int $locationId, int $courierId, bool $force = false, bool $isEmagMarketPlace = false): array` | `DELETE /api/ClientLocation/{locationId}/Courier/{courierId}` |
| `updateCourierServices(int $locationId, array $services, bool $applyToAllLocations = false, bool $patch = false): array` | `PUT` / `PATCH /api/ClientLocation/{locationId}/CourierServices` |
| `uploadLogo(int $locationId, string $base64Logo, bool $replace = false): LogoResponse` | `POST` / `PUT /api/ClientLocation/{locationId}/Logo` |
| `deleteLogo(int $locationId): LogoResponse` | `DELETE /api/ClientLocation/{locationId}/Logo` |

These endpoints declare no response schema in the Swagger. Live responses are
scalar for create (`int` id), update, and delete (`bool`); the other methods
return the decoded JSON object as `array<string, mixed>` (logos return
`LogoResponse`; `list` returns a `Collection` of raw location rows).

```php
use Daika7ana\Innoship\DTOs\Locations\LocationAddress;
use Daika7ana\Innoship\DTOs\Locations\UpdateLocation;
use Daika7ana\Innoship\DTOs\Locations\UpdateLocationCourierService;

$location = new UpdateLocation(
    name: 'Bucharest warehouse',
    externalLocationId: 'LOC-1',
    address: new LocationAddress(
        name: 'Warehouse SRL',
        contactPerson: 'Warehouse SRL',
        phone: '+40734123456',
        email: 'warehouse@example.com',
        localityId: 3502,
        countyId: 10,
        addressText: 'Str. Depozitului 5',
        postalCode: '010864',
        countryCode: 'RO',
    ),
    timeZoneId: 'Europe/Bucharest',
);

$locationId = $client->locations()->create($location);

$client->locations()->update($locationId, $location);

$client->locations()->updateCourierServices(
    locationId: $locationId,
    services: [
        new UpdateLocationCourierService(courierId: 1, serviceId: 2, enabled: true),
    ],
    applyToAllLocations: true,
);

$logo = $client->locations()->uploadLogo(
    locationId: $locationId,
    base64Logo: base64_encode(file_get_contents('logo.png')),
    replace: true,
);
```

`UpdateLocation` omits unset fields: its `id` and numeric policy fields are
nullable, so they are not sent unless you set them. `applyToAllLocations`
is a query flag; the body is the service list. `base64Logo` is the encoded
string itself — the SDK does not read the image file for you.

## Notifications — `$client->notifications()`

| Method | Endpoint |
|--------|----------|
| `sendSms(SendSmsRequest $request): array` | `POST /api/Notifications/sendsms` |
| `getByAwb(string $shipmentAwb, ?Courier $courier = null): NotificationHistoryResponse` | `GET /api/v2/notifications/by-awb` |
| `getByTemplate(int $templateId, ?string $dateFrom = null, ?string $dateTo = null): NotificationHistoryResponse` | `GET /api/v2/notifications/by-template` |

The notification endpoints use PascalCase query keys (`ShipmentAwb`,
`CourierId`, `TemplateId`, `DateFrom`, `DateTo`); the resource handles this.

```php
use Daika7ana\Innoship\DTOs\Notifications\SendSmsItem;
use Daika7ana\Innoship\DTOs\Notifications\SendSmsRequest;
use Daika7ana\Innoship\Enums\Courier;

$result = $client->notifications()->sendSms(new SendSmsRequest(
    campaignName: 'Delivery updates',
    data: [
        new SendSmsItem(
            phone: ['+40734123456', '+40734987654'],
            message: 'Your order has been dispatched.',
        ),
    ],
));

$history = $client->notifications()->getByAwb('877551879', Courier::DPD);
```

`data` is a list of items and each item's `phone` is a list of recipients that
share the same `message`.

## Offline orders — `$client->offlineOrders()`

| Method | Endpoint |
|--------|----------|
| `add(array $orders): array` | `POST /api/OfflineOrder/Add` |
| `edit(array $orders): array` | `PUT /api/OfflineOrder/Edit` |
| `activate(array $orders): array` | `PUT /api/OfflineOrder/Activate` |

Each method takes a list of DTOs (`OfflineOrderAddRequest`, `EditOrderRequest`,
`ActivateOrderRequest`) and serializes the list as the JSON body.

```php
use Daika7ana\Innoship\DTOs\OfflineOrders\OfflineOrderAddRequest;
use Daika7ana\Innoship\DTOs\OfflineOrders\OfflineOrderParcel;
use Daika7ana\Innoship\DTOs\OfflineOrders\OfflineOrderParcelSize;
use Daika7ana\Innoship\Enums\Courier;
use Daika7ana\Innoship\Enums\ParcelType;

$client->offlineOrders()->add([
    new OfflineOrderAddRequest(
        courier: Courier::DPD,
        courierShipmentId: '877551879',
        finalParcel: true,
        parcels: [
            new OfflineOrderParcel(
                sequenceNo: 1,
                size: new OfflineOrderParcelSize(width: 20.0, height: 10.0, length: 30.0),
                weight: 1.5,
                type: ParcelType::Parcel,
            ),
        ],
    ),
]);
```

`courier` is a `Courier` enum in all three request DTOs.

## Vouchers — `$client->vouchers()`

| Method | Endpoint |
|--------|----------|
| `create(CreateVoucherRequest $request): array` | `POST /api/Voucher/Create` |
| `activate(ActivateVoucherRequest $request): array` | `POST /api/Voucher/Activate` |

```php
use Daika7ana\Innoship\DTOs\Vouchers\ActivateVoucherRequest;
use Daika7ana\Innoship\DTOs\Vouchers\CreateVoucherRequest;
use Daika7ana\Innoship\Enums\Courier;

$client->vouchers()->create(new CreateVoucherRequest(
    courier: Courier::DPD,
    courierShipmentId: '877551879',
    voucherCourier: Courier::FanCourier,
    insuranceAmount: 150.0,
    insuranceCurrency: 'RON',
));

$client->vouchers()->activate(new ActivateVoucherRequest(
    courier: Courier::DPD,
    courierShipmentId: '877551879',
    courierShipmentVoucher: 'VCH-123456',
));
```

Both requests take `Courier` enums; `insuranceAmount` is a float and is only
sent with its `insuranceCurrency`.

## Repository — `$client->repository()`

| Method | Endpoint |
|--------|----------|
| `add(RepositoryAddRequest $request): array` | `POST /api/Repository` |

```php
use Daika7ana\Innoship\DTOs\Repository\RepositoryAddRequest;
use Daika7ana\Innoship\DTOs\Repository\RepositoryDocument;

$client->repository()->add(new RepositoryAddRequest(
    externalOrderId: 'EXT-1001',
    documents: [
        new RepositoryDocument(
            name: 'invoice.pdf',
            contents: base64_encode(file_get_contents('invoice.pdf')),
        ),
    ],
));
```

Documents are stored under `externalOrderId`; the Swagger notes that existing
entries are replaced.

## Low-level requests

Every resource ultimately calls:

```php
$client->request(
    method: 'POST',
    path: '/api/Order',
    body: $order,                 // Arrayable or array; null for GET
    query: ['page' => 1],         // optional
    timeout: 5.0,                 // optional
);
```

Use it for an endpoint the SDK does not wrap yet.
