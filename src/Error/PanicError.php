<?php

declare(strict_types=1);

namespace GoPhp\Error;

class PanicError extends \RuntimeException
{
    public static function nilDereference(): self
    {
        return new self('panic: invalid memory address or nil pointer dereference');
    }
}
