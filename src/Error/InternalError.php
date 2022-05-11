<?php

declare(strict_types=1);

namespace GoPhp\Error;

final class InternalError extends \LogicException
{
    public static function unreachableMethodCall(): self
    {
        return new self('Unreachable method call');
    }
}
