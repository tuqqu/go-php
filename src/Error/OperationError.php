<?php

declare(strict_types=1);

namespace GoPhp\Error;

use GoPhp\GoType\GoType;
use GoPhp\GoValue\GoValue;
use GoPhp\Operator;

final class OperationError extends \RuntimeException
{
    public static function unknownOperator(Operator $op, GoValue $value): self
    {
        return new self(
            \sprintf(
                'Unknown operator "%s" for value of type "%s"',
                $op->value,
                $value->type()->name(),
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
        if ($expected < $actual) {
            $msg = 'not enough arguments in call, expected %s, but got %d';
        } else {
            $msg = 'too many arguments in call, expected %s, but got %d';
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
}
