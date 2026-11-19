<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Tests\Unit\Resources;

use Daika7ana\Innoship\DTOs\Notifications\SendSmsItem;
use Daika7ana\Innoship\DTOs\Notifications\SendSmsRequest;
use Daika7ana\Innoship\Enums\Courier;
use Daika7ana\Innoship\Tests\Support\ResponseFixtureFactory;
use Daika7ana\Innoship\Tests\Support\TestClientFactory;
use PHPUnit\Framework\TestCase;

class NotificationsResourceTest extends TestCase
{
    public function testSendSmsSendsBody(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json(['sent' => 1]),
        );

        $result = $client->notifications()->sendSms(new SendSmsRequest(
            campaignName: 'Delivery',
            data: [new SendSmsItem(phone: ['0700000000'], message: 'Hello')],
        ));

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/Notifications/sendsms', $request->getUri()->getPath());
        $this->assertSame([
            'campaignName' => 'Delivery',
            'data' => [
                ['phone' => ['0700000000'], 'message' => 'Hello'],
            ],
        ], json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR));
        $this->assertSame(['sent' => 1], $result);
    }

    public function testGetByAwbUsesPascalCaseQueryIncludingCourierValue(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json(['notifications' => []]),
        );

        $client->notifications()->getByAwb('AWB123', Courier::Cargus);

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/v2/notifications/by-awb', $request->getUri()->getPath());

        parse_str($request->getUri()->getQuery(), $query);
        $this->assertSame('AWB123', $query['ShipmentAwb']);
        $this->assertSame('Cargus', $query['CourierId']);
    }

    public function testGetByAwbOmitsNullCourier(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json(['notifications' => []]),
        );

        $client->notifications()->getByAwb('AWB123');

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertSame('AWB123', $query['ShipmentAwb']);
        $this->assertArrayNotHasKey('CourierId', $query);
    }

    public function testGetByTemplateSendsDates(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json(['notifications' => []]),
        );

        $client->notifications()->getByTemplate(7, '2026-01-01', '2026-02-01');

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('/api/v2/notifications/by-template', $request->getUri()->getPath());

        parse_str($request->getUri()->getQuery(), $query);
        $this->assertSame('7', $query['TemplateId']);
        $this->assertSame('2026-01-01', $query['DateFrom']);
        $this->assertSame('2026-02-01', $query['DateTo']);
    }

    public function testNotificationHistoryMapsItemsAndAttachments(): void
    {
        [$client] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json([
                'notifications' => [
                    [
                        'templateName' => 'Delivery',
                        'shipmentAwb' => 'AWB123',
                        'courier' => 'Cargus',
                        'courierId' => 2,
                        'courierDisplayName' => 'Cargus',
                        'orderStatusId' => 4,
                        'orderStatus' => 'Delivered',
                        'orderStatusDate' => '2026-01-01T10:00:00Z',
                        'notificationDate' => '2026-01-01T10:01:00Z',
                        'count' => 2,
                        'status' => true,
                        'message' => 'Sent',
                        'deliveryStatus' => 'Delivered',
                        'attachments' => [['name' => 'label.pdf']],
                    ],
                ],
            ]),
        );

        $response = $client->notifications()->getByTemplate(7, null, null);

        $this->assertCount(1, $response->notifications);
        $item = $response->notifications[0];
        $this->assertSame('Delivery', $item->templateName);
        $this->assertSame('AWB123', $item->shipmentAwb);
        $this->assertSame(2, $item->courierId);
        $this->assertSame(4, $item->orderStatusId);
        $this->assertSame(2, $item->count);
        $this->assertTrue($item->status);
        $this->assertSame('Delivered', $item->deliveryStatus);
        $this->assertCount(1, $item->attachments);
        $this->assertSame('label.pdf', $item->attachments[0]->name);
    }
}
