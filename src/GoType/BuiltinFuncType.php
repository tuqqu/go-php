<?php

declare(strict_types=1);

namespace GoPhp\GoType;

final class BuiltinFuncType implements GoType
{
    public function name(): never
    {
        throw new \Exception('(built-in) must be called');
    }

    public function equals(GoType $other): bool
    {
        return $other instanceof self;
    }

    public function isCompatible(GoType $other): bool
    {
        return $other instanceof self;
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
