<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\Error\InternalError;
use GoPhp\GoValue\AddressableValue;

/**
 * Pseudo type for untyped nil, in other words, `nil` literal, which was not assigned to any variable
 */
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

    public function zeroValue(): never
    {
        throw InternalError::unreachableMethodCall();
    }

    public function isCompatible(GoType $other): bool
    {
        return $other instanceof RefType;
    }

    public function convert(AddressableValue $value): never
    {
        throw InternalError::unreachableMethodCall();
    }
}
