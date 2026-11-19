<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Resources;

use Daika7ana\Innoship\Client;
use Daika7ana\Innoship\DTOs\Repository\RepositoryAddRequest;
use Daika7ana\Innoship\Exceptions\AuthenticationException;
use Daika7ana\Innoship\Exceptions\TransportException;
use Daika7ana\Innoship\Exceptions\UnexpectedStatusException;
use Daika7ana\Innoship\Exceptions\ValidationException;
use Daika7ana\Innoship\Support\ApiResponseMapper;

class RepositoryResource
{
    public function __construct(
        private Client $client,
    ) {}

    /**
     * Add documents to the repository.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     * @return array<string, mixed>
     */
    public function add(RepositoryAddRequest $request): array
    {
        $response = $this->client->request('POST', '/api/Repository', $request);

        return ApiResponseMapper::decodeOptionalJson($response, 200);
    }
}
