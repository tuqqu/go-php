<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Array;

final class UnderlyingArray implements \Countable, \ArrayAccess, \Iterator
{
    public function __construct(
        public array $array = [],
    ) {}

    public function slice(int $offset, int $len): array
    {
        return \array_slice($this->array, $offset, $len);
    }

    public function count(): int
    {
        return \count($this->array);
    }

    public function current(): mixed
    {
        return \current($this->array);
    }

    public function next(): void
    {
        \next($this->array);
    }

    public function key(): int
    {
        return \key($this->array);
    }

    public function valid(): bool
    {
        return \current($this->array) !== false;
    }

    public function rewind(): void {}

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->array[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->array[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->array[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->array[$offset]);
    }
}
