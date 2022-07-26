<?php

declare(strict_types=1);

namespace GoPhp\Error;

use GoPhp\GoType\BasicType;
use GoPhp\GoType\GoType;
use GoPhp\GoValue\GoValue;
use GoPhp\Operator;

final class OperationError extends \RuntimeException
{
    public static function undefinedOperator(Operator $op, GoValue $value): self
    {
        $type = $value->type();
        $description = $type->name();

        if ($type instanceof BasicType) {
            $description .= ' ' . $value->toString();
        }

        return new self(
            \sprintf(
                'invalid operation: operator %s not defined on %s',
                $op->value,
                $description,
            )
        );
    }

    public static function cannotIndex(GoType $type): self
    {
        return new self(
            \sprintf('invalid operation: cannot index (%s)', $type->name())
        );
    }

    public static function cannotSlice(GoType $type): self
    {
        return new self(
            \sprintf('invalid operation: cannot slice (%s)', $type->name())
        );
    }

    public static function cannotAssign(GoValue $value): self
    {
        return new self(\sprintf('cannot assign to %s', self::valueToString($value)));
    }

    public static function invalidRangeValue(GoValue $value): self
    {
        return new self(\sprintf('cannot range over %s', self::valueToString($value)));
    }

    public static function cannotAssignToConst(GoValue $value): self
    {
        return new self(\sprintf('cannot assign to %s constant %s', $value->type()->name(), $value->toString()));
    }

    public static function lenAndCapSwapped(): self
    {
        return new self('invalid argument: length and capacity swapped');
    }

    // fixme check if this is correct
//    public static function cannotIndirect(GoType $type): self
//    {
//        return new self(\sprintf('invalid operation: cannot indirect value of type %s', $type->name()));
//    }

    public static function unsupportedOperation(string $operation, GoValue $value): self
    {
        return new self(
            \sprintf(
                'Value of type "%s" does not support "%s" operation',
                $value->type()->name(),
                $operation,
            )
        );
    }

    public static function nonFunctionCall(GoValue $value): self
    {
        return new self(
            \sprintf(
                'Cannot call non-function value of type "%s"',
                $value->type()->name(),
            )
        );
    }

    public static function expectedAssignmentOperator(Operator $op): self
    {
        return new self(
            \sprintf(
                'Unexpected operator "%s" in assignment',
                $op->value,
            )
        );
    }

    //fixme move from here
    public static function wrongArgumentNumber(int|string $expected, int $actual): self
    {
        if ($expected > $actual) {
            $msg = 'not enough arguments in call (expected %s, found %d)';
        } else {
            $msg = 'too many arguments in call (expected %s, but found %d)';
        }

        return new self(\sprintf($msg, $expected, $actual));
    }

    public static function wrongArgumentType(GoType $actual, string|GoType $expected, int $pos)
    {
        return new self(
            \sprintf(
                'invalid argument %d (%s), expected %s',
                $pos,
                $actual->name(),
                \is_string($expected) ? $expected : $expected->name(),
            )
        );
    }

    public static function cannotFullSliceString(): self
    {
        return new self('invalid operation: 3-index slice of string');
    }

    private static function valueToString(GoValue $value): string
    {
        return \sprintf(
            '%s (%s%s)',
            $value->toString(),
            $value->type()->name(),
            $value->isNamed() ? '' : ' constant',
        );
    }
}
