<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\Error\InternalError;
use GoPhp\GoValue\GoValue;

final class UntypedNilType implements RefType
{
    public function name(): string
    {
        return 'untyped nil';
    }

    public function equals(GoType $other): bool
    {
        return $this === $other;
    }

    public function reify(): RefType
    {
        return $this;
    }

    public function defaultValue(): GoValue
    {
        throw InternalError::unreachableMethodCall();
    }

    public function isCompatible(GoType $other): bool
    {
        return $other instanceof RefType;
    }

    public function convert(GoValue $value): GoValue
    {
        throw InternalError::unreachableMethodCall();
    }
}
