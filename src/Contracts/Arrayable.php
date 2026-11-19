<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Contracts;

interface Arrayable
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
