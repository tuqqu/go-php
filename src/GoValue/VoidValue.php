<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Error\InternalError;
use GoPhp\Operator;

final class VoidValue implements GoValue
{
    public function unwrap(): never
    {
        throw InternalError::unreachableMethodCall();
    }

    public function type(): never
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

    public function mutate(Operator $op, GoValue $rhs): never
    {
        throw InternalError::unreachableMethodCall();
    }

    public function equals(GoValue $rhs): never
    {
        throw InternalError::unreachableMethodCall();
    }

    public function toString(): never
    {
        throw InternalError::unreachableMethodCall();
    }

    public function copy(): never
    {
        throw InternalError::unreachableMethodCall();
    }

    public function isNamed(): never
    {
        throw InternalError::unreachableMethodCall();
    }

    public function makeNamed(): never
    {
        throw InternalError::unreachableMethodCall();
    }
}
