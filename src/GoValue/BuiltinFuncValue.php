<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\GoType\VoidType;
use GoPhp\GoType\ValueType;
use GoPhp\Operator;
use GoPhp\StreamProvider;

final class BuiltinFuncValue implements GoValue
{
    public function __construct(
        private readonly \Closure $function,
    ) {}

    public function __invoke(StreamProvider $streams, GoValue ...$argv): NoValue|TupleValue
    {
        return ($this->function)($streams, ...$argv);
    }

    public function unwrap(): callable
    {
        throw new \BadMethodCallException('cannot operate');
    }

    public function type(): ValueType
    {
        return VoidType::Builtin;
    }

    public function operate(Operator $op): never
    {
        throw new \BadMethodCallException('cannot operate');
    }

    public function operateOn(Operator $op, GoValue $rhs): never
    {
        throw new \BadMethodCallException('cannot operate');
    }

    public function equals(GoValue $rhs): BoolValue
    {
        throw new \BadMethodCallException('cannot operate');
    }
}
