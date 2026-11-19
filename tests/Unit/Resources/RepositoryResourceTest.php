<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Tests\Unit\Resources;

use Daika7ana\Innoship\DTOs\Repository\RepositoryAddRequest;
use Daika7ana\Innoship\DTOs\Repository\RepositoryDocument;
use Daika7ana\Innoship\Tests\Support\ResponseFixtureFactory;
use Daika7ana\Innoship\Tests\Support\TestClientFactory;
use PHPUnit\Framework\TestCase;

class RepositoryResourceTest extends TestCase
{
    public function testAddSendsExternalOrderIdWithDocuments(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json(['data' => []]),
        );

        $result = $client->repository()->add(new RepositoryAddRequest(
            externalOrderId: 'EXT-1',
            documents: [
                new RepositoryDocument(name: 'invoice.pdf', contents: 'base64-content'),
            ],
        ));

        $request = $httpClient->lastRequest;

        $this->assertNotNull($request);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/Repository', $request->getUri()->getPath());
        $this->assertSame([
            'externalOrderId' => 'EXT-1',
            'documents' => [
                ['name' => 'invoice.pdf', 'contents' => 'base64-content'],
            ],
        ], json_decode((string) $request->getBody(), true, flags: JSON_THROW_ON_ERROR));
        $this->assertSame(['data' => []], $result);
    }
}
