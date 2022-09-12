<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Error\TypeError;
use GoPhp\GoType\BuiltinFuncType;
use GoPhp\Operator;

/**
 * @template-implements Invokable<TypeValue|AddressableValue>
 */
final class BuiltinFuncValue implements Invokable, GoValue
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

    public function type(): BuiltinFuncType
    {
        return $this->type;
    }

    public function toString(): string
    {
        throw TypeError::builtInMustBeCalled($this->name);
    }

    public function unwrap(): callable
    {
        throw TypeError::builtInMustBeCalled($this->name);
    }

    public function operate(Operator $op): never
    {
        throw TypeError::builtInMustBeCalled($this->name);
    }

    public function operateOn(Operator $op, GoValue $rhs): never
    {
        throw TypeError::builtInMustBeCalled($this->name);
    }

    public function mutate(Operator $op, GoValue $rhs): never
    {
        throw TypeError::builtInMustBeCalled($this->name);
    }

    public function copy(): never
    {
        throw TypeError::builtInMustBeCalled($this->name);
    }
}
