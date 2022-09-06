<?php

declare(strict_types=1);

namespace GoPhp\Error;

class PanicError extends \RuntimeException
{
    public static function nilDereference(): self
    {
        return new self('panic: invalid memory address or nil pointer dereference');
    }

    public static function nilMapAssignment(): self
    {
        return new self('panic: assignment to entry in nil map');
    }
}
