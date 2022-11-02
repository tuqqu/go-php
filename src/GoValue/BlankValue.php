<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Error\InternalError;
use GoPhp\Error\RuntimeError;
use GoPhp\Operator;

/**
 * Represents runtime value of a blank identifier ("_" by default)
 */
final class BlankValue implements AddressableValue
{
    use AddressableTrait;

    public function toString(): never
    {
        throw InternalError::unreachableMethodCall();
    }

    public function operate(Operator $op): never
    {
        throw InternalError::unreachableMethodCall();
    }

    public function operateOn(Operator $op, GoValue $rhs): never
    {
        throw InternalError::unreachableMethodCall();
    }

    public function mutate(Operator $op, GoValue $rhs): void
    {
        if ($op === Operator::Eq) {
            return;
        }

        throw RuntimeError::cannotUseBlankIdent($this->getName());
    }

    public function copy(): never
    {
        throw InternalError::unreachableMethodCall();
    }

    public function unwrap(): array
    {
        throw RuntimeError::cannotUseBlankIdent($this->getName());
    }

    public function type(): never
    {
        throw InternalError::unreachableMethodCall();
    }
}
