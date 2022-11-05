<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Error\RuntimeError;
use GoPhp\Operator;

/**
 * Represents a value returned from a function with no return values.
 *
 * @template-implements GoValue<never>
 */
final class VoidValue implements GoValue
{
    public function unwrap(): never
    {
        throw RuntimeError::noValueUsedAsValue();
    }

    public function type(): never
    {
        throw RuntimeError::noValueUsedAsValue();
    }

    public function operate(Operator $op): never
    {
        throw RuntimeError::noValueUsedAsValue();
    }

    public function operateOn(Operator $op, GoValue $rhs): never
    {
        throw RuntimeError::noValueUsedAsValue();
    }

    public function mutate(Operator $op, GoValue $rhs): never
    {
        throw RuntimeError::noValueUsedAsValue();
    }

    public function toString(): never
    {
        throw RuntimeError::noValueUsedAsValue();
    }

    public function copy(): never
    {
        throw RuntimeError::noValueUsedAsValue();
    }
}
