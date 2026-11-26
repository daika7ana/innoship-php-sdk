<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Tests\Unit\Resources;

use Daika7ana\Innoship\DTOs\Labels\BulkLabelItem;
use Daika7ana\Innoship\DTOs\Labels\BulkLabelRequest;
use Daika7ana\Innoship\Enums\Courier;
use Daika7ana\Innoship\Enums\LabelFormat;
use Daika7ana\Innoship\Enums\LabelType;
use Daika7ana\Innoship\Exceptions\UnexpectedStatusException;
use Daika7ana\Innoship\Exceptions\ValidationException;
use Daika7ana\Innoship\Tests\Support\ResponseFixtureFactory;
use Daika7ana\Innoship\Tests\Support\TestClientFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class LabelsResourceTest extends TestCase
{
    /**
     * @return array<string, string>
     */
    private static function query(RequestInterface $request): array
    {
        parse_str($request->getUri()->getQuery(), $query);

        /** @var array<string, string> $query */
        return $query;
    }

    public function testGetLabelSendsDefaults(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json([
                'courier' => 1,
                'contents' => null,
                'labels' => ['pdf-1'],
            ]),
        );

        $label = $client->labels()->getLabel(1, 'AWB123');

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('/api/Label/by-courier/1/awb/AWB123', $request->getUri()->getPath());
        $this->assertSame([
            'type' => 'PDF',
            'format' => 'A6',
            'orientation' => 'portrait',
            'includeVoucher' => 'false',
            'useFile' => 'false',
            'skipCache' => 'false',
            'noReprint' => 'false',
        ], array_intersect_key(self::query($request), array_flip([
            'type',
            'format',
            'orientation',
            'includeVoucher',
            'useFile',
            'skipCache',
            'noReprint',
        ])));
        $this->assertSame(1, $label->courier);
        $this->assertSame(['pdf-1'], $label->labels->all());
    }

    public function testGetLabelAcceptsContentsOnlyPayload(): void
    {
        [$client, ] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json(['courier' => 1, 'contents' => '']),
        );

        $label = $client->labels()->getLabel(1, 'AWB123');

        $this->assertSame('', $label->contents);
        $this->assertSame([], $label->labels->all());
    }

    public function testGetLabelThrowsWhenPayloadHasNoLabel(): void
    {
        [$client, ] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json(['courier' => 1]),
        );

        $this->expectException(UnexpectedStatusException::class);
        $this->expectExceptionMessage('Innoship returned no label for this request.');

        $client->labels()->getLabel(1, 'AWB123');
    }

    public function testDownloadLabelReturnsRawFile(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => new Response(200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="label.pdf"',
            ], '%PDF-1.4 label'),
        );

        $file = $client->labels()->downloadLabel(1, 'AWB123', format: 'A4');

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('/api/Label/by-courier/1/awb/AWB123', $request->getUri()->getPath());
        $this->assertSame('true', self::query($request)['useFile']);
        $this->assertSame('A4', self::query($request)['format']);
        $this->assertSame('application/pdf', $file->contentType);
        $this->assertSame('label.pdf', $file->filename);
        $this->assertSame('%PDF-1.4 label', $file->contents());
    }

    public function testGetVoucherLabelHasNoIncludeVoucherQuery(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json(['courier' => 1, 'labels' => []]),
        );

        $client->labels()->getVoucherLabel(1, 'AWB123');

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('/api/Label/by-courier/1/voucher/AWB123', $request->getUri()->getPath());
        $this->assertArrayNotHasKey('includeVoucher', self::query($request));
    }

    public function testGetParcelLabelSendsParcelPath(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json(['courier' => 1, 'labels' => []]),
        );

        $client->labels()->getParcelLabel(1, 'AWB 123', 'BC/1');

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame(
            '/api/Label/by-courier/1/awb/AWB%20123/parcel/BC%2F1',
            $request->getUri()->getPath(),
        );
    }

    public function testGetBulkSendsOperationPath(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json(['courier' => 1, 'labels' => ['a']]),
        );

        $label = $client->labels()->getBulk('op/1');

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('/api/Label/bulk/op%2F1', $request->getUri()->getPath());
        $this->assertSame(['a'], $label->labels->all());
    }

    public function testCreateBulkSendsJsonBody(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json(['operationId' => 'op-1']),
        );

        $result = $client->labels()->createBulk(new BulkLabelRequest(
            labelType: LabelType::Pdf,
            labelFormat: LabelFormat::A6,
            awBs: [
                new BulkLabelItem(courierId: Courier::DPD, typeId: 1, courierShipmentId: '877551879'),
            ],
        ));

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('/api/Label/bulk', $request->getUri()->getPath());
        $this->assertSame(
            '{"labelType":"PDF","labelFormat":"A6","useFile":true,"includeVoucher":false,'
            . '"skipCache":false,"noReprint":false,'
            . '"awBs":[{"courierId":"DPD","typeId":1,"courierShipmentId":"877551879"}]}',
            (string) $request->getBody(),
        );
        $this->assertSame(['operationId' => 'op-1'], $result);
    }

    public function testGetCustomLabelReturnsFileResponse(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => new Response(200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="custom.pdf"',
            ], '%PDF-1.4 body'),
        );

        $file = $client->labels()->getCustomLabel(
            'tpl/1',
            Courier::DPD,
            'AWB123',
            type: LabelType::Pdf,
            format: LabelFormat::A4,
        );

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame(
            '/api/CustomLabel/template/tpl%2F1/courier/DPD/awb/AWB123',
            $request->getUri()->getPath(),
        );
        $this->assertSame([
            'type' => 'PDF',
            'format' => 'A4',
            'useFile' => 'true',
        ], array_intersect_key(self::query($request), array_flip(['type', 'format', 'useFile'])));
        $this->assertSame('application/pdf', $file->contentType);
        $this->assertSame('custom.pdf', $file->filename);
        $this->assertSame('%PDF-1.4 body', $file->contents());
    }

    public function testGetCustomParcelLabelUsesParcelPath(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => new Response(200, ['Content-Type' => 'application/pdf'], 'body'),
        );

        $client->labels()->getCustomParcelLabel('tpl', 'DPD', 'AWB123', 'BC1');

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame(
            '/api/CustomLabel/template/tpl/courier/DPD/awb/AWB123/parcel/BC1',
            $request->getUri()->getPath(),
        );
        $this->assertArrayNotHasKey('type', self::query($request));
        $this->assertArrayNotHasKey('format', self::query($request));
    }

    public function testValidationErrorThrowsValidationException(): void
    {
        [$client, ] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json(
                ['errors' => [['message' => 'Invalid awb']]],
                400,
            ),
        );

        $this->expectException(ValidationException::class);

        $client->labels()->getLabel(1, 'AWB123');
    }
}
