<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Tests\Unit\DTOs;

use Daika7ana\Innoship\DTOs\Orders\OrderContent;
use Daika7ana\Innoship\DTOs\Orders\OrderParcelSize;
use Daika7ana\Innoship\DTOs\Orders\UpdateParcelsRequest;
use PHPUnit\Framework\TestCase;

class RequestSerializationTest extends TestCase
{
    public function testUnsetOrderContentFieldsAreOmitted(): void
    {
        $this->assertSame([], (new OrderContent())->toArray());
    }

    public function testUnsetParcelSizeDimensionsAreOmitted(): void
    {
        $this->assertSame([], (new OrderParcelSize())->toArray());
    }

    public function testUnsetUpdateParcelsCountersAreOmitted(): void
    {
        $this->assertSame([], (new UpdateParcelsRequest())->toArray());
    }

    public function testExplicitValuesAreStillSerialized(): void
    {
        $content = new OrderContent(
            envelopeCount: 0,
            parcelsCount: 1,
            totalWeight: 2.5,
            oversizedPackage: false,
        );

        $this->assertSame([
            'envelopeCount' => 0,
            'parcelsCount' => 1,
            'totalWeight' => 2.5,
            'oversizedPackage' => false,
        ], $content->toArray());
    }
}
