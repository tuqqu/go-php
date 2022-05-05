<?php

declare(strict_types=1);

namespace GoPhp;

use GoPhp\Error\DefinitionError;
use GoPhp\Error\OperationError;
use GoPhp\Error\ProgramError;
use GoPhp\Error\TypeError;
use GoPhp\GoType\GoType;
use GoPhp\GoValue\Func\Params;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\NilValue;
use GoPhp\GoValue\NonRefValue;

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

function assert_types_compatible_with_cast(GoType $a, GoValue &$b): void
{
    assert_types_compatible($a, $b->type());

    if ($b instanceof NonRefValue) {
        $b = $b->reify($a);
    }
}

function assert_argc(
    array $argv,
    int $expectedArgc,
    bool $variadic = false,
    ?Params $params = null
): void {
    $actualArgc = \count($argv);
    //fixme
    $mismatch = ($variadic && $actualArgc < $expectedArgc - 1)
        || (!$variadic && $actualArgc < $expectedArgc)
        || (!$variadic && $actualArgc > $expectedArgc);

    if ($mismatch) {
        $params === null ?
            throw ProgramError::wrongBuiltinArgumentNumber($expectedArgc, $actualArgc) :
            throw ProgramError::wrongFuncArgumentNumber($argv, $params);
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
    if ($index < 0) {
        throw DefinitionError::indexNegative($index);
    }

    if ($index >= $max) {
        throw DefinitionError::indexOutOfRange($index, $max);
    }
}

function assert_slice_indices(int $cap, int $low, int $high, ?int $max = null): void
{
    //fixme revisit -1
    assert_index_exists($low, $cap);
    assert_index_exists($high - 1, $cap);

    $max ??= $cap;

    if ($low > $high) {
        throw DefinitionError::invalidSliceIndices($low, $high);
    }

    if ($high > $max) {
        throw DefinitionError::invalidSliceIndices($high, $max);
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
