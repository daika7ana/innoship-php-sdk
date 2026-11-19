<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Tests\Unit\Resources;

use Daika7ana\Innoship\DTOs\Orders\OrderContent;
use Daika7ana\Innoship\DTOs\Orders\OrderParcel;
use Daika7ana\Innoship\DTOs\Orders\OrderParcelSize;
use Daika7ana\Innoship\DTOs\Orders\OrderRequest;
use Daika7ana\Innoship\DTOs\Orders\CustomsDeclarationAttachment;
use Daika7ana\Innoship\DTOs\Orders\OrderRequestAddress;
use Daika7ana\Innoship\DTOs\Orders\UpdateParcelsRequest;
use Daika7ana\Innoship\DTOs\Orders\UpdateStatusRequest;
use Daika7ana\Innoship\DTOs\Orders\UploadDocumentsRequest;
use Daika7ana\Innoship\Enums\Courier;
use Daika7ana\Innoship\Enums\DocumentType;
use Daika7ana\Innoship\Enums\FileFormat;
use Daika7ana\Innoship\Enums\FileType;
use Daika7ana\Innoship\Enums\ParcelType;
use Daika7ana\Innoship\Enums\UploadDocumentType;
use Daika7ana\Innoship\Exceptions\AuthenticationException;
use Daika7ana\Innoship\Exceptions\UnexpectedStatusException;
use Daika7ana\Innoship\Exceptions\ValidationException;
use Daika7ana\Innoship\Tests\Support\ResponseFixtureFactory;
use Daika7ana\Innoship\Tests\Support\TestClientFactory;
use PHPUnit\Framework\TestCase;

class OrdersResourceTest extends TestCase
{
    public function testValidateReturnsBoolean(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], 'true'),
        );

        $this->assertTrue($client->orders()->validate(new OrderRequest()));

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('/api/Order/validate', $request->getUri()->getPath());
    }

    public function testValidateThrowsOnNonBooleanBody(): void
    {
        [$client, ] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json(['isValid' => true], 200),
        );

        $this->expectException(UnexpectedStatusException::class);

        $client->orders()->validate(new OrderRequest());
    }

    public function testSimulateMultipleAccepts200AndMapsResults(): void
    {
        [$client, ] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json([
                '1' => ['response' => ['courier' => 1, 'courierShipmentId' => null], 'errors' => null],
            ], 200),
        );

        $results = $client->orders()->simulateMultiple([new OrderRequest()]);

        $this->assertTrue($results->hasKey('1'));
        $this->assertSame(1, $results['1']->response?->courier);
    }

    public function testCreateSendsNestedBodyAndMapsResponse(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json(ResponseFixtureFactory::orderCreated(), 201),
        );

        $order = new OrderRequest(
            courierId: Courier::DPD,
            addressTo: new OrderRequestAddress(name: 'Jane Doe', country: 'RO', addressText: 'Main Street 1'),
            content: new OrderContent(
                parcelsCount: 1,
                totalWeight: 2.5,
                parcels: [
                    new OrderParcel(
                        sequenceNo: 1,
                        size: new OrderParcelSize(width: 10.0, height: 20.0, length: 30.0),
                        weight: 2.5,
                        type: ParcelType::Parcel,
                    ),
                ],
            ),
        );

        $response = $client->orders()->create($order);
        $request = $httpClient->lastRequest;

        $this->assertNotNull($request);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/Order', $request->getUri()->getPath());
        $this->assertSame('test-api-key', $request->getHeaderLine('X-Api-Key'));

        parse_str($request->getUri()->getQuery(), $query);
        $this->assertSame('1.0', $query['api-version']);

        $body = json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('DPD', $body['courierId']);
        $this->assertSame('Jane Doe', $body['addressTo']['name']);
        $this->assertSame(2.5, $body['content']['totalWeight']);
        $this->assertSame(10, $body['content']['parcels'][0]['size']['width']);
        $this->assertArrayNotHasKey('addressFrom', $body);
        $this->assertArrayNotHasKey('addressReturn', $body);

        $this->assertSame(662, $response->clientOrderId);
        $this->assertSame('877551879', $response->courierShipmentId);
        $this->assertSame(15.9, $response->price?->totalAmount);
        $this->assertSame('BC123', $response->courierParcelsBarcodes[0]->barcode);
    }

    public function testCreateThrowsValidationException(): void
    {
        [$client, ] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json(
                ResponseFixtureFactory::validationError('Invalid order data'),
                400,
            ),
        );

        try {
            $client->orders()->create(new OrderRequest());
            $this->fail('Expected ValidationException to be thrown.');
        } catch (ValidationException $exception) {
            $this->assertSame('Invalid order data: addressTo is required', $exception->getMessage());
            $this->assertCount(1, $exception->errors);
            $this->assertSame(['addressTo is required'], $exception->errors[0]->details->all());
            $this->assertSame('corr-123', $exception->correlationId);
        }
    }

    public function testCreateThrowsAuthenticationException(): void
    {
        [$client, ] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json(['message' => 'Unauthorized'], 401),
        );

        $this->expectException(AuthenticationException::class);

        $client->orders()->create(new OrderRequest());
    }

    public function testDeleteUsesEncodedPathAndMapsResponse(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json(
                ['courier' => 1, 'success' => true, 'message' => null],
                200,
            ),
        );

        $response = $client->orders()->delete(1, 'AWB 123');

        $this->assertTrue($response->success);
        $this->assertSame(1, $response->courier);
        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('DELETE', $request->getMethod());
        $this->assertSame('/api/Order/1/awb/AWB%20123', $request->getUri()->getPath());
    }

    public function testUpdateStatusSendsJsonList(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json([], 200),
        );

        $client->orders()->updateStatus([
            new UpdateStatusRequest(courierId: Courier::DPD, statusId: 5),
        ]);

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('/api/Order/UpdateStatus', $request->getUri()->getPath());
        $this->assertSame(
            '[{"courierId":"DPD","statusId":5}]',
            (string) $request->getBody(),
        );
    }

    public function testDownloadDocumentBuildsQueryString(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json(
                ['courier' => 1, 'contents' => null, 'documents' => ['doc-1']],
                200,
            ),
        );

        $response = $client->orders()->downloadDocument(
            1,
            'AWB123',
            DocumentType::Invoice,
            FileType::Pdf,
            FileFormat::A4,
            false,
        );

        $this->assertSame(['doc-1'], $response->documents->all());

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('/api/Order/DownloadDocument/courier/1/awb/AWB123', $request->getUri()->getPath());

        parse_str($request->getUri()->getQuery(), $query);
        $this->assertSame('Invoice', $query['document']);
        $this->assertSame('Pdf', $query['type']);
        $this->assertSame('A4', $query['format']);
        $this->assertSame('false', $query['useFile']);
    }

    public function testSimulateMultipleMapsKeysToResults(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json([
                'first' => [
                    'response' => ResponseFixtureFactory::orderCreated(),
                    'errors' => [],
                    'correlationId' => 'corr-1',
                ],
                'second' => [
                    'response' => null,
                    'errors' => [['message' => 'Invalid', 'details' => ['bad']]],
                    'correlationId' => 'corr-2',
                ],
            ], 201),
        );

        $results = $client->orders()->simulateMultiple([new OrderRequest()]);

        $this->assertTrue($results->hasKey('first'));
        $this->assertSame(662, $results['first']->response?->clientOrderId);
        $this->assertSame('corr-1', $results['first']->correlationId);
        $this->assertTrue($results->hasKey('second'));
        $this->assertNull($results['second']->response);
        $this->assertSame('Invalid', $results['second']->errors[0]->message);

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('[[]]', (string) $request->getBody());
    }

    public function testImportFileSendsMultipartAndRepeatedQueryParams(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json(['accepted' => 2], 201),
        );

        $result = $client->orders()->importFile("awb\n123\n", 'orders.csv', [1, 2]);

        $this->assertSame(['accepted' => 2], $result);

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/Order/file', $request->getUri()->getPath());
        $this->assertStringStartsWith('multipart/form-data; boundary=', $request->getHeaderLine('Content-Type'));

        $body = (string) $request->getBody();
        $this->assertStringContainsString('filename="orders.csv"', $body);
        $this->assertStringContainsString('awb', $body);

        $query = $request->getUri()->getQuery();
        $this->assertStringContainsString('userAllowedLocationIds=1', $query);
        $this->assertStringContainsString('userAllowedLocationIds=2', $query);
    }

    public function testSimulatePostsOrderAndMapsResponse(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json(ResponseFixtureFactory::orderCreated(), 200),
        );

        $response = $client->orders()->simulate(new OrderRequest(courierId: Courier::DPD));

        $this->assertSame(1, $response->courier);

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/Order/simulate', $request->getUri()->getPath());

        $body = json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('DPD', $body['courierId']);
    }

    public function testDeleteParcelEncodesPathAndMapsResponse(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json([
                'success' => true,
                'messages' => ['Parcel deleted'],
                'courierShipmentId' => 'AWB123',
                'parcels' => [],
            ], 200),
        );

        $response = $client->orders()->deleteParcel(1, 'AWB 123', 'BC/123');

        $this->assertTrue($response->success);
        $this->assertSame(['Parcel deleted'], $response->messages->all());

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('DELETE', $request->getMethod());
        $this->assertSame('/api/Order/1/awb/AWB%20123/parcel/BC%2F123', $request->getUri()->getPath());
    }

    public function testUpdateParcelsSendsJsonBodyAndMapsResponse(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json(ResponseFixtureFactory::orderCreated(), 201),
        );

        $response = $client->orders()->updateParcels(new UpdateParcelsRequest(
            courierId: Courier::DPD,
            courierShipmentId: '877551879',
            parcelsCount: 2,
            totalWeight: 3.5,
        ));

        $this->assertSame('877551879', $response->courierShipmentId);

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/Order/UpdateParcels', $request->getUri()->getPath());

        $body = json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('DPD', $body['courierId']);
        $this->assertSame(2, $body['parcelsCount']);
        $this->assertSame(3.5, $body['totalWeight']);
    }

    public function testUpdateCustomAttributesSendsAttributesAndReturnsDecodedArray(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json(['attr1' => 'value', 'attr2' => 2], 200),
        );

        $attributes = ['attr1' => 'value', 'attr2' => 2];

        $result = $client->orders()->updateCustomAttributes(1, 'AWB 123', $attributes);

        $this->assertSame($attributes, $result);

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/Order/UpdateCustomAttributes/1/awb/AWB%20123', $request->getUri()->getPath());
        $this->assertSame($attributes, json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR));
    }

    public function testGetReturnAwbInfoEncodesPathAndReturnsDecodedArray(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json(['returnAwb' => 'RET123'], 200),
        );

        $result = $client->orders()->getReturnAwbInfo(1, 'RET 123');

        $this->assertSame(['returnAwb' => 'RET123'], $result);

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/Order/ReturnAwbInfo/1/RET%20123', $request->getUri()->getPath());
    }

    public function testUploadDocumentsSendsNestedBodyAndMapsResponse(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json(['success' => true, 'message' => 'Uploaded'], 200),
        );

        $response = $client->orders()->uploadDocuments(new UploadDocumentsRequest(
            courierId: Courier::DPD,
            courierShipmentId: '877551879',
            attachments: [
                new CustomsDeclarationAttachment(
                    name: 'invoice.pdf',
                    content: 'base64-content',
                    documentType: UploadDocumentType::CommercialInvoice,
                ),
            ],
        ));

        $this->assertTrue($response->success);
        $this->assertSame('Uploaded', $response->message);

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/Order/UploadDocuments', $request->getUri()->getPath());

        $body = json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('DPD', $body['courierId']);
        $this->assertSame('invoice.pdf', $body['attachments'][0]['name']);
        $this->assertSame('CommercialInvoice', $body['attachments'][0]['documentType']);
    }
}
