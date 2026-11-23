<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Tests\Smoke;

use Daika7ana\Innoship\DTOs\Notifications\NotificationHistoryItem;
use Daika7ana\Innoship\DTOs\Notifications\NotificationHistoryResponse;
use PHPUnit\Framework\Attributes\Group;

final class NotificationsSmokeTest extends SmokeTestCase
{
    #[Group('smoke')]
    public function testNotificationHistoryByAwbAgainstLiveApi(): void
    {
        $awb = $this->requireEnvironment('INNOSHIP_SMOKE_AWB');

        $history = $this->client()->notifications()->getByAwb($awb);

        $this->assertInstanceOf(NotificationHistoryResponse::class, $history);

        foreach ($history->notifications as $notification) {
            $this->assertInstanceOf(NotificationHistoryItem::class, $notification);
        }
    }
}
