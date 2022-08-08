<?php

declare(strict_types=1);

namespace GoPhp\Error;

/**
 * Errors that indicate a bug in the code.
 * They must not occur even when running a wrongly written program.
 */
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
