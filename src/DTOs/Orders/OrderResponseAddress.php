<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\DTOs\Orders;

final readonly class OrderResponseAddress
{
    public function __construct(
        public ?LimitedAddress $from = null,
        public ?LimitedAddress $to = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            from: isset($data['from']) && is_array($data['from']) ? LimitedAddress::fromArray($data['from']) : null,
            to: isset($data['to']) && is_array($data['to']) ? LimitedAddress::fromArray($data['to']) : null,
        );
    }
}
