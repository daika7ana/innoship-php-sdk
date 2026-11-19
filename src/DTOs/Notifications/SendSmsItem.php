<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Notifications;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Support\SerializesToArray;

final readonly class SendSmsItem implements Arrayable
{
    use SerializesToArray;

    /**
     * @param list<string>|null $phone
     */
    public function __construct(
        public ?array $phone = null,
        public ?string $message = null,
    ) {}
}
