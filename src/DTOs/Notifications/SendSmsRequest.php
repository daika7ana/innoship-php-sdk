<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Notifications;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Support\SerializesToArray;

/**
 * Request body for the SMS campaign endpoint.
 */
final readonly class SendSmsRequest implements Arrayable
{
    use SerializesToArray;

    /**
     * @param list<SendSmsItem>|null $data
     */
    public function __construct(
        public ?string $campaignName = null,
        public ?array $data = null,
    ) {}
}
