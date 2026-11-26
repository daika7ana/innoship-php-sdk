# DTOs and Enums

## Two shapes

- **Request DTOs** are `final readonly` classes implementing
  `Daika7ana\Innoship\Contracts\Arrayable`. They serialize through the
  `SerializesToArray` trait: public camelCase properties become JSON keys, null
  properties are omitted, enums become their backing value, and nested DTOs and
  lists are serialized recursively.
- **Response DTOs** are `final readonly` classes with a
  `fromArray(array $data): self` factory. List fields are
  `Daika7ana\SimpleCollections\Collection` instances (iterable, countable,
  `first()`, `map()`, `all()`); nested objects are nested DTOs. Array-typed
  fields (`OrderContent::$parcels`, `OrderRequest::$customAttributes`, …) accept
  plain arrays; typed nested properties such as `addressTo` require DTO
  instances.

```php
use Daika7ana\Innoship\DTOs\Orders\OrderRequestAddress;

$address = new OrderRequestAddress(name: 'Jane Doe', country: 'RO', email: 'jane@example.com');

$address->toArray();
// ['name' => 'Jane Doe', 'country' => 'RO', 'email' => 'jane@example.com']
```

Because nulls are omitted, unset fields are not sent. Order and location
payload fields that would otherwise carry a misleading zero are nullable:
leave `OrderContent` counters/`totalWeight`, `OrderParcel::$weight`,
`OrderParcelSize` dimensions, `UpdateParcelsRequest` counters, and the
`UpdateLocation`/`LocationAddress` numeric fields unset to omit them. Other
request DTOs still use non-null scalar defaults (for example the
`OrderParameters` booleans and `OrderParcel::$sequenceNo`), so set those
deliberately.

## Enums

| Enum | Values |
|------|--------|
| `Courier` | 268 courier identifiers, e.g. `Cargus`, `DPD`, `FanCourier`, `GLS`, `Sameday`, `FanCourierMoldova`, `InPost`, `EColet` |
| `PaymentType` | `Sender`, `Recipient`, `ThirdParty` |
| `ParcelType` | `Envelope`, `Parcel`, `Pallet` |
| `LabelType` | `PDF` (case `LabelType::Pdf`), `Html`, `Zpl`, `Epl`, `Clp` |
| `LabelFormat` | `A4`, `A6`, `T_85x85`, `A6_300dpi`, `A4_4xA6`, `A6_10x9`, `A5` |
| `DocumentType` | `ConsignmentNote`, `ExpressWaybill`, `CustomsDeclaration`, `Invoice`, `WorldEase`, `ProofOfDelivery` |
| `FileType` | `Pdf`, `Zpl`, `Html` |
| `FileFormat` | `A4`, `A6`, `A5` |
| `CallCourierPickupMode` | `FixedTime`, `FirstAvailable`, `EndOfDay` |
| `MarketplaceIntegration` | `Vivre`, `Trendyol`, `SevenSenders` |
| `Incoterm` | `EXW`, `FCA`, `CPT`, `CIP`, `DAP`, `DPU`, `DDP`, `FAS`, `FOB`, `CFR`, `CIF` |
| `CustomsDeclarationIdentifierType` | `TaxID`, `BN`, `EORI`, `UKIMS`, `ZAZ`, `CPF`, `CNPJ`, `EIN`, `TIN` |
| `PurposeOfShipment` | `CommercialShipment`, `NonCommercialShipment` |
| `UploadDocumentType` | `AuthorizationForm`, `CommercialInvoice`, `CertificateOfOrigin`, `ExportAccompanyingDocument`, `ExportLicense`, `ImportPermit`, `OneTimeNAFTA`, `OtherDocument`, `PowerOfAttorney`, `PackingList`, `SEDDocument`, `ShippersLetterOfInstruction`, `Declaration` |
| `OrderOfflineStatus` | `New`, `Success`, `Error`, `InProgress`, `Consolidate` |
| `ManifestLanguage` | `Ro`, `En`, `Pl`, `Cs`, `Hu` |

The full `Courier` enum is generated from the official Swagger. It is a
string-backed enum used in request bodies; path parameters such as
`getLabel()` and `delete()` take the numeric courier id exposed as
`OrderResponse::$courier`.

## Order request tree

```
OrderRequest
├── addressFrom / addressTo / addressReturn: OrderRequestAddress
├── content: OrderContent
│   ├── parcels: OrderParcel[]
│   │   ├── size: OrderParcelSize
│   │   ├── articles: OrderArticle[]
│   │   └── dangerousGoods: DangerousGood[]
│   └── articles: OrderArticle[]
├── extra: OrderExtra
│   └── customsDeclaration: CustomsDeclaration
│       ├── exporter / importer: OrderRequestAddress
│       ├── exporterIdentifierTypes / importerIdentifierTypes: CustomsDeclarationIdentifier[]
│       └── attachments: CustomsDeclarationAttachment[]
├── parameters: OrderParameters
└── clientSettings: OrderClientSettings
    └── clientPreferences: ClientPreferences
```

## Response DTOs

| DTO | Notable fields |
|-----|----------------|
| `OrderResponse` | `clientOrderId`, `courierShipmentId`, `courier`, `courierParcelsBarcodes`, `price`, `trackPageUrl`, `trackingNumber` |
| `OrderResponsePrice` | `amount`, `vat`, `totalAmount`, `currency`, `priceComponents` |
| `PriceResponse` | `rates` (`PriceRate[]`), `correlationId` |
| `LabelResponse` | `courier`, `contents` (base64 payload), `labels`; a payload with neither key throws `UnexpectedStatusException` |
| `DocumentResponse` | `courier`, `contents`, `documents` |
| `DeleteOrderResponse` | `courier`, `success`, `message` |
| `CourierDeleteParcelsResponse` | `success`, `messages`, `courierShipmentId`, `parcels` |
| `PickupResponse` | `success`, `message` |
| `CourierLocationsResponse` | `locations` (`CourierLocationInfo[]`), `error` |
| `NotificationHistoryResponse` | `notifications` (`NotificationHistoryItem[]`) |
| `LogoResponse` | `logoUrl`, `success`, `isSuccess`, `errors` |
| `FileResponse` | `stream`, `contentType`, `filename`, `contents()` |

`FileResponse` is used for binary endpoints (`downloadLabel()`, custom labels,
manifest report).
