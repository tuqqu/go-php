<?php

declare(strict_types=1);

namespace GoPhp\Error;

use GoPhp\Arg;
use GoPhp\GoType\GoType;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\Func\FuncValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Sealable;
use GoPhp\GoValue\Sequence;
use GoPhp\GoValue\UntypedNilValue;
use GoPhp\Operator;

class OperationError extends \RuntimeException
{
    public static function undefinedOperator(Operator $op, AddressableValue $value, bool $unary = false): self
    {
        if ($op === Operator::Eq) {
            return self::cannotAssign($value);
        }

        if ($unary && $op === Operator::Mul) {
            return self::cannotIndirect($value);
        }

        return new self(
            \sprintf(
                'invalid operation: operator %s not defined on %s',
                $op->value,
                self::valueToString($value),
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

    public static function lenAndCapSwapped(): self
    {
        return new self('invalid argument: length and capacity swapped');
    }

    private static function cannotIndirect(AddressableValue $value): self
    {
        return new self(
            \sprintf(
                'invalid operation: cannot indirect %s',
                self::valueToString($value),
            ),
        );
    }

    public static function cannotTakeAddressOfMapValue(GoType $type): self
    {
        return new self(
            \sprintf(
                'invalid operation: cannot take address of value (map index expression of type %s)',
                $type->name(),
            )
        );
    }

    public static function cannotTakeAddressOfValue(GoValue $value): self
    {
        return new self(
            \sprintf(
                'invalid operation: cannot take address of %s',
                self::valueToString($value),
            )
        );
    }

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
                'invalid operation: cannot call non-function %s',
                self::valueToString($value),
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

    public static function wrongArgumentNumber(int|string $expected, int $actual): self
    {
        if ($expected > $actual) {
            $msg = 'not enough arguments in call (expected %s, found %d)';
        } else {
            $msg = 'too many arguments in call (expected %s, but found %d)';
        }

        return new self(\sprintf($msg, $expected, $actual));
    }

    public static function wrongArgumentType(Arg $arg, string|GoType $expectedType): self
    {
        return new self(
            \sprintf(
                'invalid argument %d (%s), expected %s',
                $arg->pos,
                $arg->value->type()->name(),
                \is_string($expectedType) ? $expectedType : $expectedType->name(),
            )
        );
    }

    public static function indexNegative(GoValue|int $value): self
    {
        return new self(
            \sprintf(
                'invalid argument: index %s must not be negative',
                \is_int($value) ? $value : self::valueToString($value),
            ),
        );
    }

    public static function cannotFullSliceString(): self
    {
        return new self('invalid operation: 3-index slice of string');
    }

    public static function notConstantExpr(GoValue $value): self
    {
        return new self(\sprintf('%s is not constant', self::valueToString($value)));
    }

    final protected static function valueToString(GoValue $value): string
    {
        if (!$value instanceof AddressableValue) {
            return 'value';
        }

        if ($value instanceof UntypedNilValue) {
            return $value->type()->name();
        }

        if (!$value->isAddressable()) {
            if ($value instanceof Sequence) {
                return \sprintf(
                    '%s (value of type %s)',
                    $value->toString(),
                    $value->type()->name(),
                );
            }

            return \sprintf(
                '%s (%s constant)',
                $value->toString(),
                $value->type()->name(),
            );
        }

        $isConst = $value instanceof Sealable && $value->isSealed();
        $valueString = $value instanceof FuncValue ? 'value' : $value->toString();

        if ($isConst) {
            return \sprintf(
                '%s (%s constant %s)',
                $value->getName(),
                $value->type()->name(),
                $valueString,
            );
        }

        return \sprintf(
            '%s (variable of type %s)',
            $value->getName(),
            $value->type()->name(),
        );
    }
}
