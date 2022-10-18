<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Argv;
use GoPhp\Builtin\BuiltinFunc\BuiltinFunc;
use GoPhp\Error\TypeError;
use GoPhp\GoType\BuiltinFuncType;
use GoPhp\Operator;

final class BuiltinFuncValue implements ConstInvokable, GoValue
{
    public readonly BuiltinFuncType $type;
    public readonly BuiltinFunc $func;

    public function __construct(BuiltinFunc $func)
    {
        $this->func = $func;
        $this->type = new BuiltinFuncType($func->name());
    }

    public function __invoke(Argv $argv): GoValue
    {
        return ($this->func)($argv);
    }

    public function name(): string
    {
        return $this->func->name();
    }

    public function type(): BuiltinFuncType
    {
        return $this->type;
    }

    public function toString(): string
    {
        throw TypeError::builtInMustBeCalled($this->func->name());
    }

    public function unwrap(): callable
    {
        throw TypeError::builtInMustBeCalled($this->func->name());
    }

    public function operate(Operator $op): never
    {
        throw TypeError::builtInMustBeCalled($this->func->name());
    }

    public function operateOn(Operator $op, GoValue $rhs): never
    {
        throw TypeError::builtInMustBeCalled($this->func->name());
    }

    public function mutate(Operator $op, GoValue $rhs): never
    {
        throw TypeError::builtInMustBeCalled($this->func->name());
    }

    public function copy(): never
    {
        throw TypeError::builtInMustBeCalled($this->func->name());
    }
}
