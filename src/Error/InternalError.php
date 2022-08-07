<?php

declare(strict_types=1);

namespace GoPhp\Error;

final class InternalError extends \LogicException
{
    public static function unreachableMethodCall(): self
    {
        return new self('unreachable method call');
    }

    public static function unreachable(object $context): self
    {
        return new self(\sprintf('unreachable: %s', $context::class));
    }

    public static function unknownOperator(string $operator): self
    {
        return new self(\sprintf('unknown operator: %s', $operator));
    }

    public static function jumpStackUnderflow(): self
    {
        return new self('jump stack underflow');
    }
}
