<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\GoType\GoType;
use GoPhp\Operator;

/**
 * Represents types that are passed to builtin functions as an argument
 *
 * e.g. make([]int, 2, 3)
 *           ^^^^^
 */
final class TypeValue implements GoValue
{
    public function __construct(
        public readonly GoType $type,
    ) {}

    public function unwrap(): GoType
    {
        return $this->type;
    }

    public function operate(Operator $op): never
    {
        throw new \Exception();
    }

    public function operateOn(Operator $op, GoValue $rhs): never
    {
        throw new \Exception();
    }

    public function mutate(Operator $op, GoValue $rhs): never
    {
        throw new \Exception();
    }

    public function equals(GoValue $rhs): never
    {
        throw new \Exception();
    }

    public function copy(): static
    {
        return $this;
    }

    public function type(): never
    {
        throw new \Exception();
    }

    public function toString(): never
    {
        throw new \Exception();
    }
}
