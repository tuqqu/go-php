<?php

declare(strict_types=1);

namespace GoPhp\GoType;

enum VoidType implements ValueType
{
    case NoValue;
    case Builtin;

    public function name(): never
    {
        throw new \Exception('(built-in) must be called');
    }

    public function equals(ValueType $other): bool
    {
        return $this === $other;
    }

    public function conforms(ValueType $other): bool
    {
        return $this === $other;
    }

    public function reify(): never
    {
        throw new \Exception('(built-in) must be called');
    }

    public function defaultValue(): never
    {
        throw new \Exception('(built-in) must be called');
    }
}
