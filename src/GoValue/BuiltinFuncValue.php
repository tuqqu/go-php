<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Error\InternalError;
use GoPhp\Error\TypeError;
use GoPhp\GoType\BuiltinFuncType;
use GoPhp\Operator;

final class BuiltinFuncValue implements Invocable, GoValue
{
    public readonly BuiltinFuncType $type;

    public function __construct(
        public readonly string $name,
        private readonly \Closure $function,
    ) {
        $this->type = new BuiltinFuncType($name);
    }

    public function __invoke(GoValue ...$argv): GoValue
    {
        return ($this->function)(...$argv);
    }

    public function toString(): string
    {
        throw TypeError::builtInMustBeCalled($this->name);
    }

    public function unwrap(): callable
    {
        throw TypeError::builtInMustBeCalled($this->name);
    }

    public function type(): BuiltinFuncType
    {
        return $this->type;
    }

    public function operate(Operator $op): never
    {
        throw TypeError::builtInMustBeCalled($this->name);
    }

    public function operateOn(Operator $op, GoValue $rhs): never
    {
        throw TypeError::builtInMustBeCalled($this->name);
    }

    public function equals(GoValue $rhs): BoolValue
    {
        throw TypeError::builtInMustBeCalled($this->name);
    }

    public function mutate(Operator $op, GoValue $rhs): never
    {
        throw TypeError::builtInMustBeCalled($this->name);
    }

    public function copy(): static
    {
        throw TypeError::builtInMustBeCalled($this->name);
    }

    public function isNamed(): bool
    {
        throw InternalError::unreachableMethodCall();
    }

    public function makeNamed(): void
    {
        throw InternalError::unreachableMethodCall();
    }
}
