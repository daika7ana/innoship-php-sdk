<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Resources;

use Daika7ana\Innoship\Client;
use Daika7ana\Innoship\DTOs\Common\FileResponse;
use Daika7ana\Innoship\DTOs\Manifests\ManifestRequest;
use Daika7ana\Innoship\Enums\ManifestLanguage;
use Daika7ana\Innoship\Exceptions\AuthenticationException;
use Daika7ana\Innoship\Exceptions\TransportException;
use Daika7ana\Innoship\Exceptions\UnexpectedStatusException;
use Daika7ana\Innoship\Exceptions\ValidationException;
use Daika7ana\Innoship\Support\ApiResponseMapper;

/**
 * Courier pickup manifests.
 */
class ManifestsResource
{
    public function __construct(
        private Client $client,
    ) {}

    /**
     * Request a pickup and get the manifest id.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     * @return array<string, mixed>
     */
    public function create(ManifestRequest $request): array
    {
        $response = $this->client->request('POST', '/api/Manifest', $request);

        return ApiResponseMapper::decodeOptionalJson($response, 200);
    }

    /**
     * Download a manifest report by id.
     *
     * The published Swagger declares no response schema for this endpoint, so
     * the body is returned as a raw file document.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function get(int $manifestId, ?ManifestLanguage $language = null): FileResponse
    {
        $response = $this->client->request(
            'GET',
            '/api/Manifest',
            query: [
                'manifestId' => $manifestId,
                'lang' => $language?->value,
            ],
        );

        return FileResponse::fromResponse($response);
    }
}
