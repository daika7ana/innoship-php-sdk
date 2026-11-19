<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Resources;

use Daika7ana\Innoship\Client;
use Daika7ana\Innoship\DTOs\Notifications\NotificationHistoryResponse;
use Daika7ana\Innoship\DTOs\Notifications\SendSmsRequest;
use Daika7ana\Innoship\Enums\Courier;
use Daika7ana\Innoship\Exceptions\AuthenticationException;
use Daika7ana\Innoship\Exceptions\TransportException;
use Daika7ana\Innoship\Exceptions\UnexpectedStatusException;
use Daika7ana\Innoship\Exceptions\ValidationException;
use Daika7ana\Innoship\Support\ApiResponseMapper;

class NotificationsResource
{
    public function __construct(
        private Client $client,
    ) {}

    /**
     * Send an SMS campaign.
     *
     * @return array<string, mixed>
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function sendSms(SendSmsRequest $request): array
    {
        $response = $this->client->request('POST', '/api/Notifications/sendsms', $request);

        return ApiResponseMapper::decodeOptionalJson($response, 200);
    }

    /**
     * Get the notification history for a shipment AWB.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function getByAwb(string $shipmentAwb, ?Courier $courier = null): NotificationHistoryResponse
    {
        $response = $this->client->request('GET', '/api/v2/notifications/by-awb', null, [
            'ShipmentAwb' => $shipmentAwb,
            'CourierId' => $courier?->value,
        ]);

        return NotificationHistoryResponse::fromArray(ApiResponseMapper::decodeJson($response, 200));
    }

    /**
     * Get the notification history for a template.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws UnexpectedStatusException
     * @throws TransportException
     */
    public function getByTemplate(
        int $templateId,
        ?string $dateFrom = null,
        ?string $dateTo = null,
    ): NotificationHistoryResponse {
        $response = $this->client->request('GET', '/api/v2/notifications/by-template', null, [
            'TemplateId' => $templateId,
            'DateFrom' => $dateFrom,
            'DateTo' => $dateTo,
        ]);

        return NotificationHistoryResponse::fromArray(ApiResponseMapper::decodeJson($response, 200));
    }
}
