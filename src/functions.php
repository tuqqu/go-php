<?php

declare(strict_types=1);

namespace GoPhp;

use GoPhp\Error\DefinitionError;
use GoPhp\Error\OperationError;
use GoPhp\Error\TypeError;
use GoPhp\GoType\GoType;
use GoPhp\GoValue\GoValue;

function assert_values_compatible(GoValue $a, GoValue $b): void
{
    assert_types_compatible($a->type(), $b->type());
}

function assert_types_compatible(GoType $a, GoType $b): void
{
    if (!$a->isCompatible($b)) {
        throw TypeError::incompatibleTypes($a, $b);
    }
}

function assert_argc(array $actualArgv, int $expectedArgc, bool $variadic = false): void
{
    $actualArgc = \count($actualArgv);
    if (
        $actualArgc < $expectedArgc
        || (!$variadic && $actualArgc > $expectedArgc)
    ) {
        throw OperationError::wrongArgumentNumber($expectedArgc, $actualArgc);
    }
}

function assert_arg_value(GoValue $arg, string $value, string $name, int $pos): void
{
    if (!$arg instanceof $value) {
        throw OperationError::wrongArgumentType($arg->type(), $name, $pos);
    }
}

function assert_arg_type(GoValue $arg, GoType $type, int $pos): void
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

function assert_index_value(GoValue $index, string $value, string $where): void
{
    if (!$index instanceof $value) {
        throw DefinitionError::indexOfWrongType($index, $value, $where);
    }
}

function assert_index_type(GoValue $index, GoType $type, string $where): void
{
    if (!$index->type()->equals($type)) {
        throw DefinitionError::indexOfWrongType($index, $type, $where);
    }
}
