<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Tests\Smoke;

use Daika7ana\SimpleCollections\Collection;
use PHPUnit\Framework\Attributes\Group;

final class LocationsSmokeTest extends SmokeTestCase
{
    #[Group('smoke')]
    public function testListLocationsAgainstLiveApi(): void
    {
        $locations = $this->client()->locations()->list();

        $this->assertInstanceOf(Collection::class, $locations);

        foreach ($locations as $location) {
            $this->assertIsArray($location);
        }
    }

    #[Group('smoke')]
    public function testGetLocationAgainstLiveApi(): void
    {
        $locationId = (int) $this->requireEnvironment('INNOSHIP_SMOKE_LOCATION_ID');

        $location = $this->client()->locations()->get($locationId);

        $this->assertNotEmpty($location);
    }
}
