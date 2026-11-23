<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Tests\Smoke;

use Daika7ana\Innoship\DTOs\Prices\PriceRate;
use Daika7ana\Innoship\DTOs\Prices\PriceResponse;
use Daika7ana\Innoship\Exceptions\ValidationException;
use Daika7ana\SimpleCollections\Collection;
use PHPUnit\Framework\Attributes\Group;

final class PricesSmokeTest extends SmokeTestCase
{
    #[Group('smoke')]
    public function testGetPricesAgainstLiveApi(): void
    {
        $client = $this->client();
        $serviceIds = array_values(array_unique(array_filter(
            array_merge(
                [(int) ($this->environment('INNOSHIP_SMOKE_SERVICE_ID') ?? '0')],
                $this->sampleClientLocation()['serviceIds'],
            ),
            static fn(int $serviceId): bool => $serviceId > 0,
        )));
        $serviceIds = array_slice($serviceIds, 0, 3);

        $prices = null;
        $lastNoPrices = null;

        foreach ($serviceIds as $serviceId) {
            try {
                $prices = $client->prices()->getPrices($this->sampleOrderRequest($serviceId));
                break;
            } catch (ValidationException $e) {
                if (! str_contains(strtolower($e->getMessage()), 'no prices')) {
                    throw $e;
                }

                $lastNoPrices = $e;
            }
        }

        if (! $prices instanceof PriceResponse) {
            $this->markTestSkipped(
                'The API answered "No prices available" for every tried service id; this account has no price rows for the smoke payload.'
                . ($lastNoPrices instanceof ValidationException ? ' Last response: ' . $lastNoPrices->getMessage() : ''),
            );
        }

        $this->assertInstanceOf(Collection::class, $prices->rates);
        $this->assertGreaterThan(0, $prices->rates->count());

        foreach ($prices->rates as $rate) {
            $this->assertInstanceOf(PriceRate::class, $rate);
            $this->assertGreaterThan(0, $rate->serviceId);
            $this->assertNotSame('', (string) $rate->rateCurrency);
        }
    }
}
