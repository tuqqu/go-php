<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\GoType\GoType;
use GoPhp\Operator;

final class VoidValue implements GoValue
{
    public function unwrap(): callable
    {
        throw new \BadMethodCallException('cannot operate');
    }

    public function type(): GoType
    {
        throw new \BadMethodCallException('cannot operate');
    }

    public function operate(Operator $op): never
    {
        throw new \BadMethodCallException('cannot operate');
    }

    public function operateOn(Operator $op, GoValue $rhs): never
    {
        throw new \BadMethodCallException('cannot operate');
    }

    public function mutate(Operator $op, GoValue $rhs): never
    {
        throw new \BadMethodCallException('cannot operate');
    }

    public function equals(GoValue $rhs): BoolValue
    {
        throw new \BadMethodCallException('cannot operate');
    }

    public function toString(): string
    {
        throw new \BadMethodCallException('cannot operate');
    }

    public function copy(): never
    {
        throw new \BadMethodCallException('cannot operate');
    }
}
