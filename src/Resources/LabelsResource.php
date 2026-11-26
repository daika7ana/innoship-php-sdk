<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Resources;

use Daika7ana\Innoship\Client;
use Daika7ana\Innoship\DTOs\Common\FileResponse;
use Daika7ana\Innoship\DTOs\Labels\BulkLabelRequest;
use Daika7ana\Innoship\DTOs\Labels\LabelResponse;
use Daika7ana\Innoship\Enums\Courier;
use Daika7ana\Innoship\Enums\LabelFormat;
use Daika7ana\Innoship\Enums\LabelType;
use Daika7ana\Innoship\Exceptions\AuthenticationException;
use Daika7ana\Innoship\Exceptions\TransportException;
use Daika7ana\Innoship\Exceptions\UnexpectedStatusException;
use Daika7ana\Innoship\Exceptions\ValidationException;
use Daika7ana\Innoship\Support\ApiResponseMapper;
use Psr\Http\Message\ResponseInterface;

/**
 * Label downloads and bulk label operations.
 */
class LabelsResource
{
    public function __construct(
        private Client $client,
    ) {}

    /**
     * Get the label for a courier AWB number.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function getLabel(
        int $courierId,
        string $awb,
        string $type = 'PDF',
        string $format = 'A6',
        string $orientation = 'portrait',
        bool $includeVoucher = false,
        bool $skipCache = false,
        bool $noReprint = false,
    ): LabelResponse {
        $response = $this->client->request(
            'GET',
            sprintf('/api/Label/by-courier/%d/awb/%s', $courierId, rawurlencode($awb)),
            query: [
                'type' => $type,
                'format' => $format,
                'orientation' => $orientation,
                'includeVoucher' => $includeVoucher,
                'useFile' => false,
                'skipCache' => $skipCache,
                'noReprint' => $noReprint,
            ],
        );

        return $this->labelFrom($response);
    }

    /**
     * Downloads the label for a courier AWB number as a file.
     *
     * Unlike {@see getLabel()}, which returns the base64 payload as JSON, this
     * requests the rendered file and returns its raw contents.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function downloadLabel(
        int $courierId,
        string $awb,
        string $type = 'PDF',
        string $format = 'A6',
        string $orientation = 'portrait',
        bool $includeVoucher = false,
        bool $skipCache = false,
        bool $noReprint = false,
    ): FileResponse {
        $response = $this->client->request(
            'GET',
            sprintf('/api/Label/by-courier/%d/awb/%s', $courierId, rawurlencode($awb)),
            query: [
                'type' => $type,
                'format' => $format,
                'orientation' => $orientation,
                'includeVoucher' => $includeVoucher,
                'useFile' => true,
                'skipCache' => $skipCache,
                'noReprint' => $noReprint,
            ],
        );

        return FileResponse::fromResponse($response);
    }

    /**
     * Get the voucher label for a courier AWB number.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function getVoucherLabel(
        int $courierId,
        string $awb,
        string $type = 'PDF',
        string $format = 'A6',
        string $orientation = 'portrait',
        bool $skipCache = false,
        bool $noReprint = false,
    ): LabelResponse {
        $response = $this->client->request(
            'GET',
            sprintf('/api/Label/by-courier/%d/voucher/%s', $courierId, rawurlencode($awb)),
            query: [
                'type' => $type,
                'format' => $format,
                'orientation' => $orientation,
                'useFile' => false,
                'skipCache' => $skipCache,
                'noReprint' => $noReprint,
            ],
        );

        return $this->labelFrom($response);
    }

    /**
     * Get the label for a courier parcel barcode and order AWB.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function getParcelLabel(
        int $courierId,
        string $awb,
        string $barcode,
        string $type = 'PDF',
        string $format = 'A6',
        string $orientation = 'portrait',
        bool $skipCache = false,
        bool $noReprint = false,
    ): LabelResponse {
        $response = $this->client->request(
            'GET',
            sprintf(
                '/api/Label/by-courier/%d/awb/%s/parcel/%s',
                $courierId,
                rawurlencode($awb),
                rawurlencode($barcode),
            ),
            query: [
                'type' => $type,
                'format' => $format,
                'orientation' => $orientation,
                'useFile' => false,
                'skipCache' => $skipCache,
                'noReprint' => $noReprint,
            ],
        );

        return $this->labelFrom($response);
    }

    /**
     * Create a bulk label operation.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     * @return array<string, mixed>
     */
    public function createBulk(BulkLabelRequest $request): array
    {
        $response = $this->client->request('POST', '/api/Label/bulk', $request);

        return ApiResponseMapper::decodeOptionalJson($response, 200);
    }

    /**
     * Get the result of a bulk label operation.
     *
     * The operation is asynchronous: until it finishes the API answers 400
     * with "Operation is pending", surfaced as a ValidationException.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function getBulk(string $operationId): LabelResponse
    {
        $response = $this->client->request(
            'GET',
            sprintf('/api/Label/bulk/%s', rawurlencode($operationId)),
        );

        return $this->labelFrom($response);
    }

    /**
     * Get a label rendered from a custom template.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function getCustomLabel(
        string $template,
        Courier|string $courier,
        string $awb,
        ?LabelType $type = null,
        ?LabelFormat $format = null,
        bool $useFile = true,
    ): FileResponse {
        $response = $this->client->request(
            'GET',
            sprintf(
                '/api/CustomLabel/template/%s/courier/%s/awb/%s',
                rawurlencode($template),
                rawurlencode($courier instanceof Courier ? $courier->value : $courier),
                rawurlencode($awb),
            ),
            query: [
                'type' => $type?->value,
                'format' => $format?->value,
                'useFile' => $useFile,
            ],
        );

        return FileResponse::fromResponse($response);
    }

    /**
     * Get a custom template label for one parcel of an AWB.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function getCustomParcelLabel(
        string $template,
        Courier|string $courier,
        string $awb,
        string $parcel,
        ?LabelType $type = null,
        ?LabelFormat $format = null,
        bool $useFile = true,
    ): FileResponse {
        $response = $this->client->request(
            'GET',
            sprintf(
                '/api/CustomLabel/template/%s/courier/%s/awb/%s/parcel/%s',
                rawurlencode($template),
                rawurlencode($courier instanceof Courier ? $courier->value : $courier),
                rawurlencode($awb),
                rawurlencode($parcel),
            ),
            query: [
                'type' => $type?->value,
                'format' => $format?->value,
                'useFile' => $useFile,
            ],
        );

        return FileResponse::fromResponse($response);
    }

    /**
     * A 200 body with neither label shape would otherwise map to an empty
     * LabelResponse that is indistinguishable from a real one.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    private function labelFrom(ResponseInterface $response): LabelResponse
    {
        $data = ApiResponseMapper::decodeJson($response);

        if (!array_key_exists('contents', $data) && !array_key_exists('labels', $data)) {
            throw new UnexpectedStatusException($response, 'Innoship returned no label for this request.');
        }

        return LabelResponse::fromArray($data);
    }
}
