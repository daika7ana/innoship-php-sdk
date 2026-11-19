<?php

declare(strict_types=1);

namespace Daika7ana\Innoship\Support;

use BackedEnum;
use Daika7ana\Innoship\Contracts\Arrayable;

/**
 * Serializes public properties to the camelCase JSON the API expects.
 *
 * Null properties are omitted so optional fields stay out of the payload.
 * Enums are flattened to their backing values.
 */
trait SerializesToArray
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        /** @var array<string, mixed> $properties */
        $properties = get_object_vars($this);
        $result = [];

        foreach ($properties as $key => $value) {
            $normalized = self::normalize($value);

            if ($normalized !== null) {
                $result[$key] = $normalized;
            }
        }

        return $result;
    }

    private static function normalize(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if ($value instanceof Arrayable) {
            return $value->toArray();
        }

        if (is_array($value)) {
            $items = [];

            foreach ($value as $key => $item) {
                $normalized = self::normalize($item);

                if ($normalized !== null) {
                    $items[$key] = $normalized;
                }
            }

            return $items;
        }

        return $value;
    }
}
