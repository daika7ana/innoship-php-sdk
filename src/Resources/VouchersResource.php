<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Resources;

use Daika7ana\Innoship\Client;
use Daika7ana\Innoship\DTOs\Vouchers\ActivateVoucherRequest;
use Daika7ana\Innoship\DTOs\Vouchers\CreateVoucherRequest;
use Daika7ana\Innoship\Exceptions\AuthenticationException;
use Daika7ana\Innoship\Exceptions\TransportException;
use Daika7ana\Innoship\Exceptions\UnexpectedStatusException;
use Daika7ana\Innoship\Exceptions\ValidationException;
use Daika7ana\Innoship\Support\ApiResponseMapper;

class VouchersResource
{
    public function __construct(
        private Client $client,
    ) {}

    /**
     * Create a voucher.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     * @return array<string, mixed>
     */
    public function create(CreateVoucherRequest $request): array
    {
        $response = $this->client->request('POST', '/api/Voucher/Create', $request);

        return ApiResponseMapper::decodeOptionalJson($response, 200);
    }

    /**
     * Activate a voucher.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     * @return array<string, mixed>
     */
    public function activate(ActivateVoucherRequest $request): array
    {
        $response = $this->client->request('POST', '/api/Voucher/Activate', $request);

        return ApiResponseMapper::decodeOptionalJson($response, 200);
    }
}
