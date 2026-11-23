<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Tests\Smoke;

use Daika7ana\Innoship\DTOs\Labels\LabelResponse;
use PHPUnit\Framework\Attributes\Group;

final class LabelsSmokeTest extends SmokeTestCase
{
    #[Group('smoke')]
    public function testGetLabelForKnownAwbAgainstLiveApi(): void
    {
        $courierId = (int) $this->requireEnvironment('INNOSHIP_SMOKE_COURIER_ID');
        $awb = $this->requireEnvironment('INNOSHIP_SMOKE_AWB');

        $label = $this->client()->labels()->getLabel($courierId, $awb);

        $this->assertInstanceOf(LabelResponse::class, $label);
        $this->assertTrue(
            ($label->contents !== null && $label->contents !== '') || $label->labels->count() > 0,
            'The label response carried neither base64 contents nor label parts.',
        );
    }
}
