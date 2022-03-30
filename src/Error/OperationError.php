<?php

declare(strict_types=1);

namespace GoPhp\Error;

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
}
