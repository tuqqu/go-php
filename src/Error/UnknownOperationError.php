<?php

declare(strict_types=1);

namespace GoPhp\Error;

use GoPhp\Operator;

final class UnknownOperationError extends \RuntimeException
{
    public static function unknownOperator(Operator $op): self
    {
        return new self(\sprintf('Unknown operator "%s"', $op->value));
    }
}
