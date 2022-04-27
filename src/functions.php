<?php

declare(strict_types=1);

namespace GoPhp;

use GoPhp\Error\DefinitionError;
use GoPhp\Error\OperationError;
use GoPhp\Error\TypeError;
use GoPhp\GoType\GoType;
use GoPhp\GoType\NamedType;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\NilValue;
use GoPhp\GoValue\SimpleNumber;

function assert_values_compatible(GoValue $a, GoValue $b): void
{
    assert_types_compatible($a->type(), $b->type());
}

function assert_nil_comparison(GoValue $a, GoValue $b): void
{
    if (!$b instanceof NilValue) {
        throw TypeError::onlyComparableToNil($a);
    }

    assert_values_compatible($a, $b);
}

function assert_types_compatible(GoType $a, GoType $b): void
{
    if (!$a->isCompatible($b)) {
        throw TypeError::incompatibleTypes($a, $b);
    }
}

//fixme merge with assert_types_compatible
function assert_types_compatible_with_cast(GoType $a, GoValue &$b): void
{
    assert_types_compatible($a, $b->type());

    if ($b instanceof SimpleNumber) {
        /** @var NamedType $a */
        $b = $b->convertTo($a);
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
    if (!$index->type()->isCompatible($type)) {
        throw DefinitionError::indexOfWrongType($index, $type, $where);
    }
}
