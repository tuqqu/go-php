<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Map;

use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Hashable;

/**
 * @template K of Hashable&GoValue
 * @template V of GoValue
 * @template-implements Map<K, V>
 */
final class KeyValueTupleMap implements Map
{
    /** @var array<scalar|null, array{key: K, value: V}> */
    private array $values = [];
    private int $len = 0;

    public function has(Hashable&GoValue $at): bool
    {
        return isset($this->values[$at->hash()]);
    }

    public function get(GoValue $at): GoValue
    {
        return $this->values[$at->hash()]['value'];
    }

    public function set(GoValue $value, GoValue $at): void
    {
        if (!$this->has($at)) {
            ++$this->len;
        }

        // fixme check isAddressable here
        $this->values[$at->hash()] = ['key' => $at->copy(), 'value' => $value];
    }

    public function len(): int
    {
        return $this->len;
    }

    public function delete(GoValue $at): void
    {
        unset($this->values[$at->hash()]);
    }

    /**
     * @psalm-suppress InvalidReturnType
     */
    public function iter(): iterable
    {
        foreach ($this->values as ['key' => $key, 'value' => $value]) {
            yield $key->copy() => $value;
        }
    }
}
