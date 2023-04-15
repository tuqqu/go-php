<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Array;

use ArrayAccess;
use Countable;
use Iterator;
use GoPhp\GoValue\AddressableValue;

use function array_slice;
use function count;
use function current;
use function key;
use function next;
use function reset;

/**
 * @template V of AddressableValue
 *
 * @template-implements ArrayAccess<int, V>
 * @template-implements Iterator<int, V>
 */
final class UnderlyingArray implements Countable, ArrayAccess, Iterator
{
    /** @var V[] */
    private array $array;

    /**
     * @param V[] $array
     */
    public function __construct(array $array)
    {
        foreach ($array as $value) {
            $value->makeAddressable();
        }

        $this->array = $array;
    }

    /**
     * @return self<empty>
     */
    public static function fromEmpty(): self
    {
        return new self([]);
    }

    public function copyItems(): array
    {
        $copiedItems = [];
        foreach ($this->array as $item) {
            $copiedItems[] = $item->copy();
        }

        return $copiedItems;
    }

    /**
     * @return V[]
     */
    public function slice(int $offset, int $len): array
    {
        return array_slice($this->array, $offset, $len);
    }

    /**
     * @return V[]
     */
    public function values(): array
    {
        return $this->array;
    }

    public function count(): int
    {
        return count($this->array);
    }

    /**
     * @return V
     */
    public function current(): AddressableValue
    {
        return current($this->array);
    }

    public function next(): void
    {
        next($this->array);
    }

    public function key(): int
    {
        return key($this->array);
    }

    public function valid(): bool
    {
        return current($this->array) !== false;
    }

    public function rewind(): void
    {
        reset($this->array);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->array[$offset]);
    }

    /**
     * @return V
     */
    public function offsetGet(mixed $offset): AddressableValue
    {
        return $this->array[$offset];
    }

    /**
     * @param V $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $value->makeAddressable();

        $this->array[(int) $offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->array[$offset]);
    }
}
