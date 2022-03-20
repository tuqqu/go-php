<?php

declare(strict_types=1);

namespace GoPhp\Error;

use GoParser\Ast\Operator;

final class UnknownOperationError extends \RuntimeException implements PositionAwareError
{
    use PositionAware;

    public static function unknownOperator(Operator $op): self
    {
        $error = new self(\sprintf('Unknown operator "%s"', $op->value));
        $error->position = $op->pos;

        return $error;
    }
}
