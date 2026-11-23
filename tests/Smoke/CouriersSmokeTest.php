<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Tests\Smoke;

use Daika7ana\Innoship\DTOs\Couriers\CourierLocationInfo;
use Daika7ana\Innoship\DTOs\Couriers\CourierLocationsResponse;
use PHPUnit\Framework\Attributes\Group;

final class CouriersSmokeTest extends SmokeTestCase
{
    #[Group('smoke')]
    public function testListCourierLocationsAgainstLiveApi(): void
    {
        $locationId = (int) $this->requireEnvironment('INNOSHIP_SMOKE_LOCATION_ID');
        $courierId = (int) $this->requireEnvironment('INNOSHIP_SMOKE_COURIER_ID');

        $response = $this->client()->couriers()->listLocations($locationId, $courierId);

        $this->assertInstanceOf(CourierLocationsResponse::class, $response);

        foreach ($response->locations as $location) {
            $this->assertInstanceOf(CourierLocationInfo::class, $location);
        }
    }
}
