<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Error\InternalError;
use GoPhp\Error\TypeError;
use GoPhp\Operator;

/**
 * Represents a value returned from a function with no return values.
 */
final class VoidValue implements GoValue
{
    public function unwrap(): never
    {
        throw TypeError::noValueUsedAsValue();
    }

    public function type(): never
    {
        throw TypeError::noValueUsedAsValue();
    }

    public function operate(Operator $op): never
    {
        throw TypeError::noValueUsedAsValue();
    }

    public function operateOn(Operator $op, GoValue $rhs): never
    {
        throw TypeError::noValueUsedAsValue();
    }

    public function mutate(Operator $op, GoValue $rhs): never
    {
        throw TypeError::noValueUsedAsValue();
    }

    public function equals(GoValue $rhs): never
    {
        throw TypeError::noValueUsedAsValue();
    }

    public function toString(): never
    {
        throw TypeError::noValueUsedAsValue();
    }

    public function copy(): never
    {
        throw TypeError::noValueUsedAsValue();
    }

    public function isNamed(): never
    {
        throw InternalError::unreachableMethodCall();
    }

    public function makeNamed(): never
    {
        throw InternalError::unreachableMethodCall();
    }

    public function withName(string $name): void
    {
        throw InternalError::unreachableMethodCall();
    }
}
