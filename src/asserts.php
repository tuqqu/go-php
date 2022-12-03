<?php

declare(strict_types=1);

namespace GoPhp;

use GoPhp\Builtin\BuiltinFunc\BuiltinFunc;
use GoPhp\Error\InternalError;
use GoPhp\Error\RuntimeError;
use GoPhp\GoType\GoType;
use GoPhp\GoType\NamedType;
use GoPhp\GoType\UntypedType;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\Float\FloatNumber;
use GoPhp\GoValue\Func\Func;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Hashable;
use GoPhp\GoValue\Int\IntNumber;
use GoPhp\GoValue\Castable;
use GoPhp\GoValue\UntypedNilValue;

/**
 * Asserts that two types are compatible with each other,
 * i.e. values of those types can be used in an operation.
 *
 * @internal
 *
 * @template T of GoType
 * @param T $a
 * @psalm-assert T $b
 */
function assert_types_compatible(GoType $a, GoType $b): void
{
    if (!$a->isCompatible($b)) {
        throw RuntimeError::mismatchedTypes($a, $b);
    }
}

/**
 * Asserts that the two values can be used in one operation.
 * Shorthand for `assert_types_compatible`, but with values.
 *
 * @internal
 *
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
 *
 * @psalm-assert !UntypedNilValue $b
 */
function assert_nil_comparison(GoValue $a, GoValue $b, string $name = ''): void
{
    assert_values_compatible($a, $b);

    if (!$b instanceof UntypedNilValue) {
        throw RuntimeError::onlyComparableToNil($name);
    }
}

/**
 * @internal
 * @psalm-assert AddressableValue $b
 */
function assert_types_compatible_with_cast(GoType $a, GoValue &$b): void
{
    assert_types_compatible($a, $b->type());

    if ($b instanceof Castable && $a instanceof NamedType) {
        $b = $b->cast($a);
    }

    if (!$b instanceof AddressableValue) {
        throw InternalError::unexpectedValue($b);
    }
}

/**
 * Assert the number of arguments passed to a function
 *
 * @internal
 */
function assert_argc(Func|BuiltinFunc $context, Argv $argv, int $expectedArgc, bool $variadic = false): void
{
    $mismatch = ($variadic && $argv->argc < $expectedArgc - 1) || (!$variadic && $argv->argc !== $expectedArgc);

    if (!$mismatch) {
        return;
    }

    if ($context instanceof BuiltinFunc) {
        throw RuntimeError::wrongBuiltinArgumentNumber($expectedArgc, $argv->argc, $context->name());
    }

    throw RuntimeError::wrongFuncArgumentNumber($argv, $context->type->params);
}

/**
 * @internal
 *
 * @template C
 * @psalm-param class-string<C> $value
 * @psalm-assert Arg<C> $arg
 */
function assert_arg_value(Arg $arg, string $value, string $name): void
{
    if (!$arg->value instanceof $value) {
        throw RuntimeError::wrongArgumentType($arg, $name);
    }
}

/**
 * @internal
 *
 * @psalm-assert Arg<IntNumber|FloatNumber> $arg
 */
function assert_arg_int(Arg $arg): void
{
    if (
        !$arg->value instanceof IntNumber
        && ($arg->value instanceof FloatNumber && $arg->value->type() !== UntypedType::UntypedRoundFloat)
    ) {
        throw RuntimeError::wrongArgumentType($arg, 'int');
    }
}

/**
 * @internal
 *
 * @psalm-assert Arg<IntNumber|FloatNumber> $arg
 */
function assert_arg_float(Arg $arg): void
{
    if (
        !$arg->value instanceof FloatNumber
        && !$arg->value instanceof IntNumber
    ) {
        throw RuntimeError::wrongArgumentType($arg, 'float');
    }
}

/**
 * @internal
 */
function assert_arg_type(Arg $arg, GoType $type): void
{
    if (!$type->isCompatible($arg->value->type())) {
        throw RuntimeError::wrongArgumentType($arg, $type->name());
    }
}

/**
 * @internal
 */
function assert_index_exists(int $index, int $max): void
{
    assert_index_positive($index);

    if ($index >= $max) {
        throw RuntimeError::indexOutOfRange($index, $max);
    }
}

/**
 * @internal
 *
 * @psalm-assert positive-int $index
 */
function assert_index_positive(int $index): void
{
    if ($index < 0) {
        throw RuntimeError::indexNegative($index);
    }
}

/**
 * @internal
 */
function assert_index_sliceable(int $cap, int $low, int $high, ?int $max = null): void
{
    //fixme revisit -1 weird cases
    assert_index_exists($low, $cap);
    assert_index_exists($high - 1, $cap);

    $max ??= $cap;

    if ($low > $high) {
        throw RuntimeError::invalidSliceIndices($low, $high);
    }

    if ($high > $max) {
        throw RuntimeError::invalidSliceIndices($high, $max);
    }
}

/**
 * @internal
 *
 * @psalm-assert IntNumber|FloatNumber $index
 */
function assert_index_int(GoValue $index, string $context): void
{
    if (
        !$index instanceof IntNumber
        && $index->type() !== UntypedType::UntypedRoundFloat
    ) {
        throw RuntimeError::indexOfWrongType($index, IntNumber::NAME, $context);
    }
}

/**
 * @internal
 */
function assert_index_type(GoValue $index, GoType $type, string $context): void
{
    if (!$index->type()->isCompatible($type)) {
        throw RuntimeError::indexOfWrongType($index, $type->name(), $context);
    }
}

/**
 * @internal
 *
 * @psalm-assert Hashable $index
 */
function assert_map_key(GoValue $index): void
{
    if (!$index instanceof Hashable) {
        throw RuntimeError::invalidMapKeyType($index->type());
    }
}
