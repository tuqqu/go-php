<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Builtin\BuiltinFunc\BuiltinFunc;
use GoPhp\Error\TypeError;
use GoPhp\GoType\BuiltinFuncType;
use GoPhp\Operator;

/**
 * @template-implements Invokable<TypeValue|AddressableValue>
 */
final class BuiltinFuncValue implements Invokable, GoValue
{
    public readonly BuiltinFuncType $type;
    private readonly BuiltinFunc $func;

    public function __construct(BuiltinFunc $func)
    {
        $this->func = $func;
        $this->type = new BuiltinFuncType($func->name());
    }

    public function __invoke(GoValue ...$argv): GoValue
    {
        return ($this->func)(...$argv);
    }

    public function name(): string
    {
        return $this->func->name();
    }

    public function expectsTypeAsFirstArg(): bool
    {
        return $this->func->expectsTypeAsFirstArg();
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
