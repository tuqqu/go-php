<?php

declare(strict_types=1);

namespace GoPhp;

use GoPhp\Error\DefinitionError;
use GoPhp\Error\OperationError;
use GoPhp\Error\ProgramError;
use GoPhp\Error\TypeError;
use GoPhp\GoType\GoType;
use GoPhp\GoType\UntypedType;
use GoPhp\GoValue\Float\BaseFloatValue;
use GoPhp\GoValue\Func\Params;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Int\BaseIntValue;
use GoPhp\GoValue\UntypedNilValue;
use GoPhp\GoValue\NonRefValue;

/**
 * Asserts that two types are compatible with each other,
 * i.e. values of those types can be used in an operation.
 *
 * @internal
 * @template T of GoType
 * @param T $a
 * @psalm-assert T $b
 */
function assert_types_compatible(GoType $a, GoType $b): void
{
    if (!$a->isCompatible($b)) {
        throw TypeError::mismatchedTypes($a, $b);
    }
}

/**
 * Asserts that the two values can be used in one operation.
 * Shorthand for `assert_types_compatible`, but with values.
 *
 * @internal
 * @template V of GoValue
 * @param V $a
 * @psalm-assert V $b
 */
function assert_values_compatible(GoValue $a, GoValue $b): void
{
    assert_types_compatible($a->type(), $b->type());
}

/**
 * Assertion for operations with `nil`
 *
 * @internal
 * @psalm-assert !UntypedNilValue $b
 */
function assert_nil_comparison(GoValue $a, GoValue $b, string $name = ''): void
{
    assert_values_compatible($a, $b);

    if (!$b instanceof UntypedNilValue) {
        throw TypeError::onlyComparableToNil($name);
    }
}

/**
 * @internal
 * @psalm-assert NonRefValue $b
 */
function assert_types_compatible_with_cast(GoType $a, GoValue &$b): void
{
    assert_types_compatible($a, $b->type());

    if ($b instanceof NonRefValue) {
        $b = $b->reify($a);
    }
}

/**
 * @internal
 */
function assert_argc(
    array $argv,
    int $expectedArgc,
    bool $variadic = false,
    ?Params $params = null
): void {
    $actualArgc = \count($argv);
    //fixme empty with variadic e.g. make()
    $mismatch = ($variadic && $actualArgc < $expectedArgc - 1)
        || (!$variadic && $actualArgc < $expectedArgc)
        || (!$variadic && $actualArgc > $expectedArgc);

    if ($mismatch) {
        $params === null ?
            throw ProgramError::wrongBuiltinArgumentNumber($expectedArgc, $actualArgc) :
            throw ProgramError::wrongFuncArgumentNumber($argv, $params);
    }
}

/**
 * @template C
 * @psalm-param class-string<C> $value
 * @psalm-assert C $arg
 */
function assert_arg_value(GoValue $arg, string $value, string $name, int $pos): void
{
    if (!$arg instanceof $value) {
        throw OperationError::wrongArgumentType($arg->type(), $name, $pos);
    }
}

/**
 * @psalm-assert BaseIntValue $arg
 */
function assert_arg_int(GoValue $arg, int $pos) {
    if (
        !$arg instanceof BaseIntValue
        && ($arg instanceof BaseFloatValue && $arg->type() !== UntypedType::UntypedRoundFloat)
    ) {
        throw OperationError::wrongArgumentType($arg->type(), 'int', $pos);
    }
}

function assert_arg_type(GoValue $arg, GoType $type, int $pos): void
{
    if (!$type->isCompatible($arg->type())) {
        throw OperationError::wrongArgumentType($arg->type(), $type->name(), $pos);
    }
}

function assert_index_exists(int $index, int $max): void
{
    assert_index_positive($index);

    if ($index >= $max) {
        throw DefinitionError::indexOutOfRange($index, $max);
    }
}

/**
 * @psalm-assert positive-int $index
 */
function assert_index_positive(int $index): void
{
    if ($index < 0) {
        throw OperationError::indexNegative($index);
    }
}

function assert_index_sliceable(int $cap, int $low, int $high, ?int $max = null): void
{
    //fixme revisit -1 weird cases
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

/**
 * @psalm-assert BaseIntValue|BaseFloatValue $index
 */
function assert_index_int(GoValue $index, string $context): void
{
    if (
        !$index instanceof BaseIntValue
        && $index->type() !== UntypedType::UntypedRoundFloat
    ) {
        throw DefinitionError::indexOfWrongType($index, BaseIntValue::NAME, $context);
    }
}

function assert_index_type(GoValue $index, GoType $type, string $context): void
{
    if (!$index->type()->isCompatible($type)) {
        throw DefinitionError::indexOfWrongType($index, $type->name(), $context);
    }
}
