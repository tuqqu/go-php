<?php

declare(strict_types=1);

namespace GoPhp;

use GoPhp\Error\DefinitionError;
use GoPhp\Error\OperationError;
use GoPhp\Error\TypeError;
use GoPhp\GoType\ValueType;
use GoPhp\GoValue\GoValue;

function assert_values_compatible(GoValue $a, GoValue $b): void
{
    assert_types_compatible($a->type(), $b->type());
}

function assert_types_compatible(ValueType $a, ValueType $b): void
{
    if (!$a->isCompatible($b)) {
        throw TypeError::incompatibleTypes($a, $b);
    }
}

function assert_argc(array $actualArgv, int $expectedArgc, bool $variadic = false): void
{
    $actualArgc = \count($actualArgv);
    if (
        $actualArgc < $expectedArgc || (!$variadic && $actualArgc > $expectedArgc)) {
        throw OperationError::wrongArgumentNumber($expectedArgc, $actualArgc);
    }
}

function assert_arg_value(GoValue $arg, string $value, string $name, int $pos): void
{
    if (!$arg instanceof $value) {
        throw OperationError::wrongArgumentType($arg->type(), $name, $pos);
    }
}

function assert_arg_type(GoValue $arg, ValueType $type, int $pos): void
{
    if (!$arg->type()->isCompatible($type)) {
        throw OperationError::wrongArgumentType($arg->type(), $type->name(), $pos);
    }
}

function assert_index_exists(int $index, int $max): void
{
    if ($index >= $max || $index < 0) {
        throw DefinitionError::indexOutOfRange($index, $max);
    }
}
