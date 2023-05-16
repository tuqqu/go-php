<?php

declare(strict_types=1);

namespace GoPhp\Error;

use GoParser\Lexer\Position;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\String\UntypedStringValue;
use RuntimeException;

use function sprintf;

class PanicError extends RuntimeException implements GoError
{
    public readonly AddressableValue $panicValue;

    public function __construct(AddressableValue $panicValue)
    {
        parent::__construct(sprintf('panic: %s', $panicValue->toString()));

        $this->panicValue = $panicValue;
    }

    public static function nilDereference(): self
    {
        return new self(new UntypedStringValue('runtime error: invalid memory address or nil pointer dereference'));
    }

    public static function nilMapAssignment(): self
    {
        return new self(new UntypedStringValue('assignment to entry in nil map'));
    }

    public function getPosition(): ?Position
    {
        return null;
    }
}
