# Errors

All exceptions extend `Daika7ana\Innoship\Exceptions\InnoshipException`.

| HTTP status | Exception |
|-------------|-----------|
| `401` | `AuthenticationException` |
| `400`, `422` | `ValidationException` |
| Any other non-2xx | `UnexpectedStatusException` |

| Exception | Raised when |
|-----------|-------------|
| `AuthenticationException` | API key is empty, or the API answers `401` |
| `ValidationException` | The API answers `400` or `422` |
| `UnexpectedStatusException` | Any other unexpected status, including `403` and `5xx` |
| `TransportException` | The HTTP layer fails (DNS, timeout, TLS, connection reset) |

A `403` stays an `UnexpectedStatusException`; its `$e->error->isAuthenticationRelated()`
is `true` (the predicate covers `401` and `403`).

`TransportException` implements PSR-18 `ClientExceptionInterface`.

`AuthenticationException` and `ValidationException` thrown from an HTTP
response expose the raw PSR-7 response on `$e->response` (null when the
exception is raised before a response exists). `UnexpectedStatusException::$response`
is nullable and defaults to `null`, so an unexpected status can be raised
without a response in scope. Exceptions that carry a response also expose the
numeric `Retry-After` value in seconds on `$e->retryAfter` (`null` when absent,
non-numeric, or an HTTP date).

All three HTTP exceptions expose the parsed error payload on `$e->error` (an
`InnoshipError`) when the failure came from an API error response.

`$e->getCode()` is the HTTP status when a response is present, otherwise `0`;
carrier error codes stay on `$e->error->code`.

## Validation errors

Innoship returns:

```json
{
  "errors": [
    {
      "message": "Invalid order data",
      "details": ["addressTo is required"],
      "categoryCode": "Validation",
      "subcategoryCode": "Order"
    }
  ],
  "correlationId": "corr-123"
}
```

The SDK maps it to `ValidationException`:

```php
use Daika7ana\Innoship\DTOs\Orders\OrderRequest;
use Daika7ana\Innoship\Exceptions\ValidationException;

$request = new OrderRequest();

try {
    $client->orders()->create($request);
} catch (ValidationException $e) {
    $e->getMessage();       // "Invalid order data: addressTo is required"
    $e->correlationId;      // "corr-123"
    $e->errors;             // list<ErrorResponseItem>
    $e->response;           // raw PSR-7 ResponseInterface (nullable)
    $e->retryAfter;         // int|null, from the Retry-After header
    $e->error;              // InnoshipError|null, parsed payload

    foreach ($e->errors as $error) {
        $error->message;
        $error->details;
        $error->categoryCode;
        $error->subcategoryCode;
    }
}
```

ASP.NET model-binding payloads (`{"errors":{"field":["message"]}}`) are
flattened into the same `ErrorResponseItem` list.

## Unexpected responses

`UnexpectedStatusException` exposes the PSR-7 response when one is available:

```php
use Daika7ana\Innoship\Exceptions\UnexpectedStatusException;

try {
    $client->locations()->get(999);
} catch (UnexpectedStatusException $e) {
    $e->response?->getStatusCode();
    $e->error?->message;
}
```

## InnoshipError

`Daika7ana\\Innoship\\DTOs\\Common\\Errors\\InnoshipError` is the shared error DTO
carried by the exceptions above:

- `code` (HTTP status or the payload `code`), `message`, `id` (payload `id` or
  `correlationId`), `raw` (decoded payload).
- `context` (from the first error's `categoryCode`) and `component` (from the
  first error's `subcategoryCode`) are carrier-defined: their values come from
  the API payload only, so treat them as opaque labels and do not branch on
  specific values.
- `message` falls back through `message`, `error`, `general_error`, the first
  error's `message`, then `Innoship API error.`.
- `isValidationRelated()` is true for `400`/`422` or a non-empty `errors` array;
  `isAuthenticationRelated()` is true for `401`/`403` or auth-related messages.
