# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
