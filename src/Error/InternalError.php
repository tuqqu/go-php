<?php

declare(strict_types=1);

namespace GoPhp\Error;

use LogicException;
use ReflectionClass;

use function get_debug_type;
use function is_object;
use function is_string;
use function sprintf;

/**
 * Errors that indicate a bug in the code.
 * They must not occur even when running a wrongly written program.
 *
 * Non-implemented yet features also throw this exception.
 */
final class InternalError extends LogicException
{
    public static function unreachableMethodCall(): self
    {
        return new self('unreachable method call');
    }

    public static function unreachable(object|string|null $context): self
    {
        $context = match (true) {
            is_object($context) => (new ReflectionClass($context))->getShortName(),
            is_string($context) => $context,
            $context === null => '',
        };

        return new self('unreachable' . ($context ? sprintf(': %s', $context) : ''));
    }

    public static function unexpectedValue(mixed $value, ?string $expected = null): self
    {
        $message = sprintf('unexpected value: %s', get_debug_type($value));

        if ($expected !== null) {
            $message .= sprintf(', expected: %s', $expected);
        }

        return new self($message);
    }

    public static function unknownOperator(string $operator): self
    {
        return new self(sprintf('unknown operator: %s', $operator));
    }

    public static function jumpStackUnderflow(): self
    {
        return new self('jump stack underflow');
    }

    public static function unimplemented(): self
    {
        return new self('not yet implemented');
    }
}
