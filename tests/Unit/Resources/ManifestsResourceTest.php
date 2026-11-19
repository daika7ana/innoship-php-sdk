<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Tests\Unit\Resources;

use Daika7ana\Innoship\DTOs\Manifests\ManifestRequest;
use Daika7ana\Innoship\Enums\Courier;
use Daika7ana\Innoship\Enums\ManifestLanguage;
use Daika7ana\Innoship\Tests\Support\ResponseFixtureFactory;
use Daika7ana\Innoship\Tests\Support\TestClientFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ManifestsResourceTest extends TestCase
{
    public function testCreatePostsJsonBody(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => ResponseFixtureFactory::json(['manifestId' => 42]),
        );

        $result = $client->manifests()->create(new ManifestRequest(
            courierId: Courier::DPD,
            externalLocationId: 'loc-1',
            lastPickHour: 15,
        ));

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/Manifest', $request->getUri()->getPath());
        $this->assertSame(
            '{"courierId":"DPD","externalLocationId":"loc-1","lastPickHour":15}',
            (string) $request->getBody(),
        );
        $this->assertSame(['manifestId' => 42], $result);
    }

    public function testGetSendsQueryAndReturnsFileResponse(): void
    {
        [$client, $httpClient] = TestClientFactory::create(
            static fn() => new Response(200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "attachment; filename*=UTF-8''manifest%20ro.pdf",
            ], '%PDF body'),
        );

        $file = $client->manifests()->get(42, ManifestLanguage::Ro);

        $request = $httpClient->lastRequest;
        $this->assertNotNull($request);
        $this->assertSame('/api/Manifest', $request->getUri()->getPath());
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertSame('42', $query['manifestId']);
        $this->assertSame('Ro', $query['lang']);
        $this->assertSame('application/pdf', $file->contentType);
        $this->assertSame('manifest ro.pdf', $file->filename);
        $this->assertSame('%PDF body', $file->contents());
    }
}
