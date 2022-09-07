<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Error\InternalError;
use GoPhp\Error\OperationError;
use GoPhp\GoType\GoType;
use GoPhp\Operator;

/**
 * Represents types in environment
 * as well as type "values", that are passed to some built-in functions as arguments
 *
 * e.g. make([]int, 2, 3)
 *           ^^^^^
 *
 * @template-implements Invocable<TypeValue|AddressableValue>
 */
final class TypeValue implements Invocable, GoValue
{
    public function __construct(
        public readonly GoType $type,
    ) {}

    public function __invoke(GoValue ...$argv): GoValue
    {
        $value = match (\count($argv)) {
            1 => $argv[0],
            0 => throw new OperationError(\sprintf('missing argument in conversion to %s', $this->type->name())),
            default => throw new OperationError(\sprintf('too many arguments in conversion to %s', $this->type->name())),
        };

        return $this->type->convert($value);
    }

    public function unwrap(): GoType
    {
        return $this->type;
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

    public function copy(): self
    {
        return $this;
    }

    public function type(): GoType
    {
        return $this->type;
    }

    public function toString(): never
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
