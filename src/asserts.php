<?php

declare(strict_types=1);

namespace GoPhp;

use GoPhp\Builtin\BuiltinFunc\BuiltinFunc;
use GoPhp\Error\DefinitionError;
use GoPhp\Error\OperationError;
use GoPhp\Error\ProgramError;
use GoPhp\Error\TypeError;
use GoPhp\GoType\GoType;
use GoPhp\GoType\UntypedType;
use GoPhp\GoValue\Float\BaseFloatValue;
use GoPhp\GoValue\Func\Func;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Int\BaseIntValue;
use GoPhp\GoValue\NonRefValue;
use GoPhp\GoValue\UntypedNilValue;

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
 */
function assert_types_compatible_with_cast(GoType $a, GoValue &$b): void
{
    assert_types_compatible($a, $b->type());

    if ($b instanceof NonRefValue) {
        $b = $b->reify($a);
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
        throw ProgramError::wrongBuiltinArgumentNumber($expectedArgc, $argv->argc, $context->name());
    }

    throw ProgramError::wrongFuncArgumentNumber($argv, $context->type->params);
}

/**
 * @internal
 * @template C
 * @psalm-param class-string<C> $value
 * @psalm-assert Arg<C> $arg
 */
function assert_arg_value(Arg $arg, string $value, string $name): void
{
    if (!$arg->value instanceof $value) {
        throw OperationError::wrongArgumentType($arg, $name);
    }
}

/**
 * @internal
 * @psalm-assert Arg<BaseIntValue|BaseFloatValue> $arg
 */
function assert_arg_int(Arg $arg): void
{
    if (
        !$arg->value instanceof BaseIntValue
        && ($arg->value instanceof BaseFloatValue && $arg->value->type() !== UntypedType::UntypedRoundFloat)
    ) {
        throw OperationError::wrongArgumentType($arg, 'int');
    }
}

/**
 * @internal
 * @psalm-assert Arg<BaseIntValue|BaseFloatValue> $arg
 */
function assert_arg_float(Arg $arg): void
{
    if (
        !$arg->value instanceof BaseFloatValue
        && !$arg->value instanceof BaseIntValue
    ) {
        throw OperationError::wrongArgumentType($arg, 'float');
    }
}

/**
 * @internal
 */
function assert_arg_type(Arg $arg, GoType $type): void
{
    if (!$type->isCompatible($arg->value->type())) {
        throw OperationError::wrongArgumentType($arg, $type->name());
    }
}

/**
 * @internal
 */
function assert_index_exists(int $index, int $max): void
{
    assert_index_positive($index);

    if ($index >= $max) {
        throw DefinitionError::indexOutOfRange($index, $max);
    }
}

/**
 * @internal
 * @psalm-assert positive-int $index
 */
function assert_index_positive(int $index): void
{
    if ($index < 0) {
        throw OperationError::indexNegative($index);
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
        throw DefinitionError::invalidSliceIndices($low, $high);
    }

    if ($high > $max) {
        throw DefinitionError::invalidSliceIndices($high, $max);
    }
}

/**
 * @internal
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

/**
 * @internal
 */
function assert_index_type(GoValue $index, GoType $type, string $context): void
{
    if (!$index->type()->isCompatible($type)) {
        throw DefinitionError::indexOfWrongType($index, $type->name(), $context);
    }
}
