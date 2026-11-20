# AGENTS.md

## Project

Typed PHP 8.3+ SDK for the Innoship shipping API (`daika7ana/innoship-php-sdk`,
namespace `Daika7ana\Innoship`). Library only: no server, no web entrypoint, no
framework in `require`. PSR-18 core, one resource class per API area, readonly
DTOs, enums.

Base URL `https://api.innoship.com` (`ClientConfig::BASE_URL`); paths are
`/api/...`. The SDK covers only the published
`https://api.innoship.com/swagger/v1/swagger.json` contract; read-only
`query.innoship.com` endpoints are intentionally out of scope.

## Commands

```bash
composer install
composer ci         # validate --strict, pint --test, phpstan, phpunit tests/Unit
composer pint       # style check only, never fixes
composer stan       # PHPStan level 8 over src/
composer test       # full PHPUnit suite
composer test:unit  # tests/Unit only
vendor/bin/pint     # the only way to auto-fix style
```

- `composer.lock` is gitignored, so CI resolves fresh; keep `guzzlehttp/psr7` at
  `>=2` because `Client::create()` instantiates `GuzzleHttp\Psr7\HttpFactory`.
- Quality gates: PHPStan level 8 over `src/` (vendor/tests excluded), Laravel
  Pint (`per` preset), PHPUnit strict (`.dist`), `composer validate --strict`.
  CI runs validate, Pint and PHPStan on PHP 8.3 and unit tests on 8.3/8.4/8.5,
  branch `master`.

## Layout

`src/` holds `Client.php`, `Config/`, `Contracts/`, `Http/`, `Support/`,
`Exceptions/`, `Enums/`, `DTOs/<Area>/` and `Resources/`. `tests/Support/` holds
the fakes, `tests/Unit/` mirrors `src/`, `tests/Fixtures/` the raw JSON;
`docs/` holds the user guides.

## Conventions

- Never import Symfony, Laravel, or another framework into `src/`, `tests/`, or
  docs, and never make one a runtime dependency; framework integration is out of
  scope. No new runtime dependencies without an explicit decision. Requests are
  built through the injected PSR-17 factories and absolute URIs assembled
  internally; callers never pass a URL.
- Request DTO: `final readonly class X implements Arrayable`, `use
  SerializesToArray`, public camelCase properties matching the JSON keys, nulls
  omitted. Response DTO: `final readonly` with `fromArray(array $data): self`;
  list fields are `Daika7ana\SimpleCollections\Collection`.
- Resources call `$this->client->request(METHOD, PATH, $body, $query)` and map
  with `ApiResponseMapper`; docblock `@throws` for the four exceptions. Dynamic
  path segments use `rawurlencode`. Never introduce snake_case keys.
- `docs/` is user-facing documentation: behavior changes go in the matching file,
  not just in code. `CHANGELOG.md` is Keep a Changelog, newest first, tags `vX.Y.Z`.
- Commits and PR titles use Conventional Commits (`<type>(<scope>): <subject>`,
  lowercase imperative, no trailing period).

## HTTP Transport And Timeouts

- `Client` builds requests with the injected PSR-17 `RequestFactoryInterface` /
  `StreamFactoryInterface`, consumes PSR-7 `ResponseInterface`, and depends only
  on PSR-18 `Psr\Http\Client\ClientInterface` — never a concrete client.
- `src/Http/HttpClientInterface.php` widens PSR-18 with
  `sendRequest(RequestInterface $request, ?float $timeout = null)`.
  `src/Http/GuzzleHttpClient.php` is the bundled transport (Guzzle `http_errors`
  and redirects disabled, so 4xx/5xx reach the mappers);
  `src/Http/Psr18HttpClientAdapter.php` wraps any other PSR-18 client, rejects a
  non-null per-call timeout with `InvalidArgumentException`, and rethrows a
  foreign `ClientExceptionInterface` as `TransportException` (original as
  `previous`). `TransportException` implements `ClientExceptionInterface`.
- `Client::__construct()` throws `InvalidArgumentException` when
  `ClientConfig::timeout` is non-null and the client is not this package's
  `HttpClientInterface`. `ClientConfig::timeout` is `?float`, default `null`;
  `connectTimeout` exists only on
  `GuzzleHttpClient::__construct(?GuzzleClient $guzzleClient = null, ?float $timeout = null, ?float $connectTimeout = null)`,
  which applies `DEFAULT_TIMEOUT = 30.0` / `DEFAULT_CONNECT_TIMEOUT = 10.0` only
  when it creates its own Guzzle client. Precedence: per-call
  `Client::send($request, $t)` → `ClientConfig::timeout` → transport constructor
  arguments → 30.0/10.0.
- `guzzlehttp/guzzle` and `guzzlehttp/psr7` are runtime dependencies (default
  transport and `GuzzleHttp\Psr7\HttpFactory`), so `Client::create()` needs no
  arguments; no `class_exists` guard, no `suggest` entry.

## API Quirks

- Auth is `X-Api-Key`; `api-version=1.0` is sent as both header and query.
  `createRequest()` throws `AuthenticationException` without a key; the env key is
  `INNOSHIP_API_KEY`. 400/422 → `ValidationException`, 401 →
  `AuthenticationException`, everything else unexpected → `UnexpectedStatusException`.
- `POST /api/Order`, `/api/Order/simulate`, `/api/Order/multiple/simulate`,
  `/api/Order/UpdateParcels` and `/api/Price` answer 201, so those resources
  accept `[201, 200]`; `/api/Order/validate` accepts `[200, 201]`;
  `POST /api/Order/file` accepts only 201; delete endpoints accept `[201, 200]`.
  All other endpoints accept only 200.
- Notification history query keys are PascalCase (`ShipmentAwb`, `CourierId`,
  `TemplateId`, `DateFrom`, `DateTo`).
- `POST /api/Order/file` is undocumented in Swagger: multipart field `file` with
  `Content-Type: text/csv`, filename stripped of CR/LF/quotes/backslashes, plus
  repeated `userAllowedLocationIds` query parameters.
- Schema-less endpoints return `array<string, mixed>` through
  `ApiResponseMapper::decodeOptionalJson`; empty DTOs serialize as `{}`.

## Tests

- Unit tests never touch the network. `TestClientFactory::create($handler)`
  returns `[Client, FakeHttpClient]`; `ResponseFixtureFactory` has canned payloads
  and `tests/Fixtures/` holds raw JSON samples.
- Assert method, path, query, body and headers on `$httpClient->lastRequest`;
  every new endpoint needs a test covering both request and response mapping.
- `composer ci` must pass before a change is done.
