<?php

declare(strict_types=1);

namespace GoPhp\GoType;

enum VoidType implements GoType
{
    case NoValue;
    case Builtin;

    public function name(): never
    {
        throw new \Exception('(built-in) must be called');
    }

    public function equals(GoType $other): bool
    {
        return $this === $other;
    }

    public function isCompatible(GoType $other): bool
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
