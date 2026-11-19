# AGENTS.md

## Project

Typed PHP SDK for the Innoship shipping API. Published as
`daika7ana/innoship-php-sdk`, namespace `Daika7ana\Innoship`, PHP 8.3+.

Modeled on `daika7ana/ecolet-php-sdk`: a small PSR-18 client core, one resource
class per API area, readonly DTOs, and enums generated from the Swagger.

## Commands

```bash
composer install
composer ci         # pint --test, phpstan, phpunit tests/Unit
composer pint       # style check
composer stan       # PHPStan level 8 over src/
composer test       # full PHPUnit suite
composer test:unit  # unit tests only
vendor/bin/pint     # auto-fix style
```

## Layout

```
src/
  Client.php                 PSR-18 request builder + lazy resource accessors
  Config/ClientConfig.php    baseUrl, apiKey, apiVersion, acceptLanguage, timeout
  Http/                      HttpClientInterface + GuzzleHttpClient adapter
  Contracts/Arrayable.php    toArray() contract for request DTOs
  Support/                   SerializesToArray, JsonHelper, ApiResponseMapper
  Exceptions/                InnoshipException + auth/validation/status/transport
  Enums/                     one backed enum per documented API enum
  DTOs/<Area>/               request DTOs (Arrayable) and response DTOs (fromArray)
  Resources/                 one resource class per API area
tests/
  Support/                   FakeHttpClient, TestClientFactory, ResponseFixtureFactory
  Unit/                      mirrors src/
docs/                        user guides
```

## Conventions

- Request DTO: `final readonly class X implements Arrayable`, `use SerializesToArray`,
  public camelCase properties matching the JSON keys exactly, nulls omitted.
- Response DTO: `final readonly class X` with `public static function fromArray(array $data): self`;
  list fields are `Daika7ana\SimpleCollections\Collection` instances.
- Resource: `public function __construct(private Client $client) {}`; call
  `$this->client->request(METHOD, PATH, $body, $query)` and map with
  `ApiResponseMapper`; docblock every public method with `@throws` for the four
  Innoship exceptions.
- URL-encode dynamic path segments with `rawurlencode`.
- Endpoints without a response schema return `array<string, mixed>` through
  `ApiResponseMapper::decodeOptionalJson`.
- Never introduce snake_case JSON keys; the API is camelCase.
- No new runtime dependencies.

## API quirks

- Auth is `X-Api-Key`; `api-version=1.0` must be sent as both header and query.
- 400/422 → `ValidationException` (`errors` + `correlationId`); 401 →
  `AuthenticationException`; everything else unexpected →
  `UnexpectedStatusException`.
- `POST /api/Order`, `/simulate`, `/multiple/simulate`, `/UpdateParcels` and
  `/api/Price` answer `201`; those resources accept `[201, 200]`. `validate`
  accepts `[200, 201]`. `POST /api/Order/file` accepts only `201`. Order delete
  endpoints accept `[201, 200]` (observed `200`). All other endpoints accept
  only `200`.
- Notification history query keys are PascalCase (`ShipmentAwb`, `CourierId`,
  `TemplateId`, `DateFrom`, `DateTo`).
- `POST /api/Order/file` has no request schema in Swagger; use multipart field
  `file` and repeated `userAllowedLocationIds` query parameters.
- The SDK covers only `https://api.innoship.com/swagger/v1/swagger.json`.
  Read-only `query.innoship.com` endpoints are intentionally out of scope.

## Tests

- Unit tests never hit the network.
- Use `TestClientFactory::create($handler)`; assert against
  `$httpClient->lastRequest` (PSR-7 request) and response DTOs.
- Every new endpoint needs a test covering method, path, query/body, and
  response mapping.
- `composer ci` must pass before a change is considered done.
