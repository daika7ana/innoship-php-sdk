<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Tests\Smoke;

use Daika7ana\Innoship\Client;
use Daika7ana\Innoship\Config\ClientConfig;
use Daika7ana\Innoship\DTOs\Orders\OrderContent;
use Daika7ana\Innoship\DTOs\Orders\OrderParcel;
use Daika7ana\Innoship\DTOs\Orders\OrderParcelSize;
use Daika7ana\Innoship\DTOs\Orders\OrderRequest;
use Daika7ana\Innoship\DTOs\Orders\OrderRequestAddress;
use Daika7ana\Innoship\Enums\ParcelType;
use Daika7ana\Innoship\Enums\PaymentType;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

abstract class SmokeTestCase extends TestCase
{
    private ?Client $client = null;

    /** @var array{externalLocationId: string, address: OrderRequestAddress, serviceIds: list<int>}|null */
    private ?array $resolvedClientLocation = null;

    protected function client(): Client
    {
        if ($this->client instanceof Client) {
            return $this->client;
        }

        $config = ClientConfig::fromEnvironment();

        if ($config->apiKey === '') {
            $this->markTestSkipped('Set INNOSHIP_API_KEY to run smoke tests.');
        }

        $this->client = Client::create(config: $config);

        return $this->client;
    }

    protected function environment(string $name): ?string
    {
        $value = getenv($name);

        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    protected function requireEnvironment(string $name): string
    {
        $value = $this->environment($name);

        if ($value === null) {
            $this->markTestSkipped(sprintf('Set %s to run this smoke test.', $name));
        }

        return $value;
    }

    protected function requireWrites(): void
    {
        if (filter_var(getenv('INNOSHIP_SMOKE_ENABLE_WRITES'), FILTER_VALIDATE_BOOLEAN) !== true) {
            $this->markTestSkipped('Set INNOSHIP_SMOKE_ENABLE_WRITES=true to run write smoke tests.');
        }
    }

    /**
     * @return array{externalLocationId: string, address: OrderRequestAddress, serviceIds: list<int>}
     */
    protected function sampleClientLocation(): array
    {
        if ($this->resolvedClientLocation !== null) {
            return $this->resolvedClientLocation;
        }

        foreach ($this->client()->locations()->list() as $summary) {
            if (! is_array($summary)) {
                continue;
            }

            $externalLocationId = $summary['externalLocationId'] ?? null;
            $locationId = $summary['id'] ?? null;

            if (! is_string($externalLocationId) || $externalLocationId === '' || ! is_numeric($locationId)) {
                continue;
            }

            $location = $this->client()->locations()->get((int) $locationId);
            $address = $location['address'] ?? null;
            $services = $location['courierServices'] ?? null;

            if (($location['ignorePricesWhenMissing'] ?? false) === true || ! is_array($address) || ! is_array($services)) {
                continue;
            }

            $serviceIds = [];

            foreach ($services as $service) {
                if (is_array($service) && is_numeric($service['serviceId'] ?? null)) {
                    $serviceIds[] = (int) $service['serviceId'];
                }
            }

            if ($serviceIds === []) {
                continue;
            }

            return $this->resolvedClientLocation = [
                'externalLocationId' => $externalLocationId,
                'address' => new OrderRequestAddress(
                    name: (string) ($address['name'] ?? 'Smoke Test Warehouse'),
                    contactPerson: (string) ($address['contactPerson'] ?? $address['name'] ?? 'Smoke Test Warehouse'),
                    country: (string) ($address['countryCode'] ?? 'RO'),
                    countyId: is_numeric($address['countyId'] ?? null) ? (int) $address['countyId'] : null,
                    localityId: is_numeric($address['localityId'] ?? null) ? (int) $address['localityId'] : null,
                    addressText: (string) ($address['addressText'] ?? ''),
                    postalCode: (string) ($address['postalCode'] ?? ''),
                    phone: (string) ($address['phone'] ?? ''),
                    email: (string) ($address['email'] ?? ''),
                ),
                'serviceIds' => array_values(array_unique($serviceIds)),
            ];
        }

        $this->markTestSkipped('No client location with an external id and a courier service was found for INNOSHIP_API_KEY.');
    }

    /**
     * Order payload for the live price and order checks, built from the first
     * client location that can actually be priced.
     */
    protected function sampleOrderRequest(?int $serviceId = null): OrderRequest
    {
        $location = $this->sampleClientLocation();
        $today = (new DateTimeImmutable('today'))->format('Y-m-d');

        return new OrderRequest(
            serviceId: $serviceId
                ?? (int) ($this->environment('INNOSHIP_SMOKE_SERVICE_ID') ?? (string) ($location['serviceIds'][0] ?? 1)),
            shipmentDate: $today,
            shipmentDateEnd: $today,
            externalClientLocation: $location['externalLocationId'],
            addressFrom: $location['address'],
            addressTo: new OrderRequestAddress(
                name: 'Jane Doe',
                contactPerson: 'Jane Doe',
                country: 'RO',
                countyId: 10,
                localityId: 3502,
                addressText: 'Str. Exemplu 10',
                postalCode: '010101',
                phone: '+40734123456',
                email: 'jane@example.com',
            ),
            payment: PaymentType::Sender,
            content: new OrderContent(
                parcelsCount: 1,
                totalWeight: 1.0,
                contents: 'Books',
                parcels: [
                    new OrderParcel(
                        sequenceNo: 1,
                        weight: 1.0,
                        type: ParcelType::Parcel,
                        size: new OrderParcelSize(width: 20.0, height: 10.0, length: 30.0),
                        reference1: 'SMOKE-1',
                    ),
                ],
            ),
        );
    }
}
