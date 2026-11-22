# Errors

All exceptions extend `Daika7ana\Innoship\Exceptions\InnoshipException`.

| Exception | Raised when |
|-----------|-------------|
| `AuthenticationException` | API key is empty, or the API answers `401` |
| `ValidationException` | The API answers `400` or `422` |
| `UnexpectedStatusException` | Any other unexpected status, including `403` and `5xx` |
| `TransportException` | The HTTP layer fails (DNS, timeout, TLS, connection reset) |

`TransportException` implements PSR-18 `ClientExceptionInterface`.

`AuthenticationException` and `ValidationException` thrown from an HTTP
response expose the raw PSR-7 response on `$e->response` (null when the
exception is raised before a response exists). `UnexpectedStatusException`
always has one. Exceptions that carry a response also expose the numeric
`Retry-After` value in seconds on `$e->retryAfter` (`null` when absent,
non-numeric, or an HTTP date).

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

`UnexpectedStatusException` keeps the PSR-7 response:

```php
use Daika7ana\Innoship\Exceptions\UnexpectedStatusException;

try {
    $client->locations()->get(999);
} catch (UnexpectedStatusException $e) {
    $e->response->getStatusCode();
    (string) $e->response->getBody();
}
```
