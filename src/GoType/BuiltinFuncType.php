<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\Error\InternalError;
use GoPhp\GoValue\GoValue;

final class BuiltinFuncType implements GoType
{
    public function __construct(
        private readonly string $name,
    ) {}

    public function name(): never
    {
        throw InternalError::unreachableMethodCall();
    }

    public function equals(GoType $other): bool
    {
        return $other instanceof self
            && $this->name === $other->name;
    }

    public function isCompatible(GoType $other): bool
    {
        return $this->equals($other);
    }

    public function reify(): never
    {
        throw InternalError::unreachableMethodCall();
    }

    public function defaultValue(): never
    {
        throw InternalError::unreachableMethodCall();
    }

    public function convert(GoValue $value): GoValue
    {
        throw InternalError::unreachableMethodCall();
    }
}
