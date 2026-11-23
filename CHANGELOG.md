# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.3.0] - 2026-11-23

### Added

- Live smoke coverage for prices, labels, courier locations and notification history, plus an opt-in (`INNOSHIP_SMOKE_ENABLE_WRITES=true`) order simulate/create/delete workflow that fails when cleanup fails.

- `InnoshipError` DTO (`code`, `message`, `id`, `raw`, `context`, `component`) exposing the parsed API error payload on `AuthenticationException`, `ValidationException` and `UnexpectedStatusException` as `$exception->error`.
- `test:smoke` composer script and self-skipping `tests/Smoke` suite; tests skip unless `INNOSHIP_API_KEY` is set.

### Changed

- **Breaking:** `ValidationException` constructor reordered to the sibling shape: `response`, `message`, `code`, `retryAfter`, `error`, `correlationId`, with `errors` and `previous` last.
- `getCode()` returns the HTTP status when a response is present, otherwise `0`; carrier codes stay on `$error->code`.
- `UnexpectedStatusException` from `OrdersResource::validate()` and `LocationsResource::create()` now carries the parsed `$error`.
- **Breaking:** `UnexpectedStatusException::$response` is nullable and defaults to `null`; the default message is `Unexpected HTTP status` when no response is in scope.

### Removed

- Empty `src/Auth/` and `tests/Unit/Auth/` directories.

### Fixed

- README and Testing docs no longer claim there is no live smoke suite; both describe `composer test:smoke` and the `INNOSHIP_API_KEY` skip.

## [1.2.0] - 2026-11-22

### Added

- `Client::withTimeout(?float $timeout)` derives a client sharing the transport and PSR-17 factories with a different request timeout budget — no new transport is built. `ClientConfig::withTimeout()` returns a config with that timeout.
- `AuthenticationException` and `ValidationException` expose the raw PSR-7 response on `$exception->response` when the failure came from an HTTP response, plus the numeric `Retry-After` value in seconds on `$exception->retryAfter` (`null` when absent); `UnexpectedStatusException` also exposes `$exception->retryAfter`.

## [1.1.0] - 2026-11-20

### Added

- `Client::create()` and `Client::__construct()` accept any PSR-18 `ClientInterface`; a bare client is wrapped in `Psr18HttpClientAdapter`.
- `GuzzleHttpClient::DEFAULT_TIMEOUT`/`DEFAULT_CONNECT_TIMEOUT` and `timeout:`/`connectTimeout:` constructor arguments.

### Changed

- **Breaking:** `ClientConfig::timeout` defaults to `null` instead of `30.0`; the bundled transport still applies 30 s request / 10 s connect. A custom `HttpClientInterface` receives `null` unless a timeout is configured.
- A non-null `ClientConfig::timeout` combined with a bare PSR-18 client throws `InvalidArgumentException`, and a per-call timeout passed to such a client throws too.
- `OrdersResource::importFile()` builds the multipart body directly instead of using `GuzzleHttp\Psr7\MultipartStream`: random boundary, `Content-Type: text/csv` on the file part, and CR/LF, quotes and backslashes stripped from the filename.

## [1.0.0] - 2026-11-19

### Added

- Initial release of the typed Innoship PHP SDK.
- `Client` with API-key authentication (`X-Api-Key` or `INNOSHIP_API_KEY`), a PSR-18 transport, and lazy resources.
- `orders()` — create, simulate, validate, bulk simulate, file import, delete orders and parcels, update statuses/parcels/custom attributes, upload and download documents, return AWB info.
- `prices()` — request rates for an order payload.
- `labels()` — `getLabel()` (base64 JSON), `downloadLabel()` (raw file), voucher and parcel labels, bulk label operations, custom templates.
- `manifests()` — create a manifest and download its report.
- `couriers()` — courier pickup, courier location listing, courier location ID generation and assignment.
- `locations()` — client location CRUD, summary/validate, courier services and logos.
- `notifications()` — SMS sending and notification history.
- `offlineOrders()` — add, edit and activate offline orders.
- `vouchers()` — create and activate vouchers.
- `repository()` — append documents by external order ID.
- Request DTOs with `toArray()` serialization for every documented payload, typed response DTOs, and enums for all documented enum types.
- `Collection`-typed list fields (via `daika7ana/simple-collections`) on response DTOs and list-returning resource methods.
- PHPStan level 8, Laravel Pint, and a PHPUnit unit suite wired into `composer ci`.
- Sensible transport defaults: 30-second request timeout and 10-second connect cap.
- Empty request DTOs serialize as `{}` instead of `[]`.
- Exceptions from custom `HttpClientInterface` implementations are wrapped in `TransportException`.
- `validate()` throws when the API returns a non-boolean body instead of silently reporting `false`.
- Shared `FileResponse::fromResponse()` for label and manifest downloads.
- Live-payload contract tests for price and order money fields.
- Order and location request DTOs omit unset counters, weights, dimensions, and policy fields instead of sending `0`/`false` defaults.
- `LabelType::Pdf` serializes as `PDF`, matching the base label methods.
- Client location create/update/delete map the live scalar responses (`int` id, `bool`) instead of expecting a JSON object.
