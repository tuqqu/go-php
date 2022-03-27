<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\GoType\ValueType;
use GoPhp\GoType\VoidType;
use GoPhp\Operator;

enum NoValue implements GoValue
{
    case NoValue;

    public function unwrap(): callable
    {
        throw new \BadMethodCallException('cannot operate');
    }

    public function type(): ValueType
    {
        return VoidType::NoValue;
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
}
