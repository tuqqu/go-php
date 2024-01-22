<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Argv;
use GoPhp\Error\InternalError;
use GoPhp\Error\RuntimeError;
use GoPhp\GoType\GoType;
use GoPhp\Operator;

/**
 * Represents types in environment
 * as well as type "values", that are passed to some built-in functions as arguments
 *
 * ```
 * e.g. make([]int, 2, 3)
 *           ^^^^^
 *```
 *
 * @template-implements AddressableValue<GoType>
 */
final class TypeValue implements ConstInvokable, AddressableValue
{
    use AddressableTrait;

    public const string NAME = 'type';

    public function __construct(
        public readonly GoType $type,
    ) {}

    public function __invoke(Argv $argv): GoValue
    {
        return match ($argv->argc) {
            1 => $this->type->convert($argv[0]->value),
            0 => throw RuntimeError::missingConversionArg($this->type),
            default => throw RuntimeError::tooManyConversionArgs($this->type),
        };
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
}
