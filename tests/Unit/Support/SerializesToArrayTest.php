<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Tests\Unit\Support;

use Daika7ana\Innoship\Contracts\Arrayable;
use Daika7ana\Innoship\Enums\ParcelType;
use Daika7ana\Innoship\Support\SerializesToArray;
use PHPUnit\Framework\TestCase;

class SerializesToArrayTest extends TestCase
{
    public function testSkipsNullsAndFlattensEnumsAndLists(): void
    {
        $dto = new SerializesToArrayTestFlat();

        $this->assertSame([
            'name' => 'Box',
            'oversized' => false,
            'type' => 'Parcel',
            'references' => ['a', 'b'],
        ], $dto->toArray());
    }

    public function testSerializesNestedArrayable(): void
    {
        $dto = new SerializesToArrayTestParent(new SerializesToArrayTestNested('child'));

        $this->assertSame(['child' => ['value' => 'child']], $dto->toArray());
    }
}

final class SerializesToArrayTestFlat implements Arrayable
{
    use SerializesToArray;

    public ?string $name = 'Box';

    public ?string $note = null;

    public bool $oversized = false;

    public ParcelType $type = ParcelType::Parcel;

    /** @var list<string> */
    public array $references = ['a', 'b'];
}

final class SerializesToArrayTestParent implements Arrayable
{
    use SerializesToArray;

    public function __construct(
        public ?SerializesToArrayTestNested $child = null,
    ) {}
}

final class SerializesToArrayTestNested implements Arrayable
{
    use SerializesToArray;

    public function __construct(
        public string $value,
    ) {}
}
