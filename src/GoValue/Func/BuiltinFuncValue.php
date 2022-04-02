<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Func;

use GoPhp\GoType\VoidType;
use GoPhp\GoType\ValueType;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\GoValue;
use GoPhp\Operator;
use GoPhp\Stream\StreamProvider;

final class BuiltinFuncValue implements Invocable, GoValue
{
    public function __construct(
        private readonly \Closure $function,
    ) {}

    public function __invoke(StreamProvider $streams, GoValue ...$argv): GoValue
    {
        return ($this->function)($streams, ...$argv);
    }

    public function toString(): string
    {
        throw new \BadMethodCallException('cannot operate');
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

    public function mutate(Operator $op, GoValue $rhs): never
    {
        throw new \BadMethodCallException('cannot operate');
    }

    public function copy(): static
    {
        throw new \BadMethodCallException('cannot operate');
    }
}
