<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\GoValue\GoValue;

final class TupleType implements ValueType
{
    public readonly array $types;
    public readonly string $name;

    /**
     * @param ValueType[] $types
     */
    public function __construct(
        array $types,
    )
    {
        $this->types = $types;
        $this->name = \sprintf(
            '(%s)',
            self::typesToString($types),
        );
    }

    public function name(): string
    {
        return $this->name;
    }

    public function reify(): static
    {
        return $this;
    }

    public function defaultValue(): GoValue
    {
        //fixme
    }

    public function equals(ValueType $other): bool
    {
        return $other instanceof self && $this->name === $other->name;
    }

    public function conforms(ValueType $other): bool
    {
        return $this->equals($other);
    }

    /**
     * @param ValueType[] $types
     */
    private static function typesToString(array $types): string
    {
        $str = '';
        foreach ($types as $type) {
            $str .= $type->name();
            $str .= ','; //fixme comma
        }

        return $str;
    }
}
