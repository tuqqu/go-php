<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\GoType\BuiltinFuncType;
use GoPhp\Operator;

final class BuiltinFuncValue implements Invocable, GoValue
{
    public function __construct(
        private readonly \Closure $function,
    ) {}

    public function __invoke(GoValue ...$argv): GoValue
    {
        return ($this->function)(...$argv);
    }

    public function toString(): string
    {
        throw new \BadMethodCallException('cannot operate');
    }

    public function unwrap(): callable
    {
        throw new \BadMethodCallException('cannot operate');
    }

    public function type(): BuiltinFuncType
    {
        return new BuiltinFuncType();
    }

    public function operate(Operator $op): AddressValue
    {
        if ($op === Operator::BitAnd) {
            return new AddressValue($this);
        }

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
