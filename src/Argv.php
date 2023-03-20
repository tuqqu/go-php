<?php

declare(strict_types=1);

namespace GoPhp;

use ArrayAccess;
use Countable;
use GoPhp\Error\InternalError;

use function count;

/**
 * @template-implements ArrayAccess<int, Arg>
 */
final class Argv implements ArrayAccess, Countable
{
    /**
     * @param list<Arg> $values
     */
    public function __construct(
        public readonly array $values,
        public readonly int $argc = 0,
    ) {}

    public static function fromEmpty(): self
    {
        return new self([]);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->values[$offset]);
    }

    public function offsetGet(mixed $offset): Arg
    {
        return $this->values[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw InternalError::unreachableMethodCall();
    }

    public function offsetUnset(mixed $offset): void
    {
        throw InternalError::unreachableMethodCall();
    }

    /**
     * Value is different from $argc for variadic arguments.
     *
     * for f(a, b), $argc == 2, count($argv) == 2
     * for f(a, b...), $argc == 2, count($argv) == 1 + length of b
     */
    public function count(): int
    {
        return count($this->values);
    }
}
