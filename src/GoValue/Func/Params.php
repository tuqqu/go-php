<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Func;

use GoPhp\Error\InternalError;

final class Params implements \Countable, \ArrayAccess, \Iterator
{
    private readonly array $params;
    private readonly int $len;
    private int $pos = 0;

    /**
     * @param Param[] $params
     */
    public function __construct(array $params)
    {
        $this->params = $params;
        $this->len = \count($params);
    }

    public function void(): bool
    {
        return $this->len === 0;
    }

    public function count(): int
    {
        return $this->len;
    }

    public function current(): ?Param
    {
        return $this->params[$this->pos];
    }

    public function next(): void
    {
        ++$this->pos;
    }

    public function key(): int
    {
        return $this->pos;
    }

    public function valid(): bool
    {
        return isset($this->params[$this->pos]);
    }

    public function rewind(): void
    {
        $this->pos = 0;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->params[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->params[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): never
    {
        throw new InternalError('Cannot modify params object');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new InternalError('Cannot modify params object');
    }
}
