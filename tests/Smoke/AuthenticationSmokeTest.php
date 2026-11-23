<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Tests\Smoke;

use PHPUnit\Framework\Attributes\Group;

final class AuthenticationSmokeTest extends SmokeTestCase
{
    #[Group('smoke')]
    public function testApiKeyAuthenticatesAgainstLiveApi(): void
    {
        $client = $this->client();

        $this->assertNotSame('', $client->getConfig()->apiKey);

        $request = $client->createRequest('GET', '/api/ClientLocation');
        $this->assertSame($client->getConfig()->apiKey, $request->getHeaderLine('X-Api-Key'));

        $response = $client->send($request);

        $this->assertSame(200, $response->getStatusCode());
    }
}
