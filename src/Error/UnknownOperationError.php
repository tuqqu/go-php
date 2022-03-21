<?php

declare(strict_types=1);

namespace GoPhp\Error;

use GoPhp\Operator;

final class UnknownOperationError extends \RuntimeException
{
//    use PositionAwa/re;

    public static function unknownOperator(Operator $op): self
    {
        $error = new self(\sprintf('Unknown operator "%s"', $op->value));
//        $error->position = $op->pos;

        return $error;
    }
}
