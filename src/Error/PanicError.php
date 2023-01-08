<?php

declare(strict_types=1);

namespace GoPhp\Error;

use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\StringValue;

class PanicError extends \RuntimeException
{
    public readonly AddressableValue $panicValue;

    public function __construct(AddressableValue $panicValue)
    {
        parent::__construct(\sprintf('panic: %s', $panicValue->toString()));

        $this->panicValue = $panicValue;
    }

    public static function nilDereference(): self
    {
        return new self(new StringValue('runtime error: invalid memory address or nil pointer dereference'));
    }

    public static function nilMapAssignment(): self
    {
        return new self(new StringValue('assignment to entry in nil map'));
    }
}
