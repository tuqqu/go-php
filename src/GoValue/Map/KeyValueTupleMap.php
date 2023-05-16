<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Map;

use GoPhp\Error\InternalError;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Hashable;

/**
 * @psalm-type Key = Hashable&GoValue
 *
 * @template K of Key
 * @template V of GoValue
 *
 * @template-implements Map<K, V>
 *
 * psalm bug with Intersection types in generics
 * @psalm-suppress PossiblyUndefinedMethod
 */
final class KeyValueTupleMap implements Map
{
    /** @var array<scalar, array{key: K, value: V}> */
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

    public function set(GoValue $value, Hashable&GoValue $at): void
    {
        if (!$this->has($at)) {
            ++$this->len;
        }

        $at = $at->copy();

        /** @var K $at */
        if (!$at instanceof AddressableValue) {
            throw InternalError::unexpectedValue($at);
        }

        $this->values[$at->hash()] = ['key' => $at, 'value' => $value];
    }

    public function len(): int
    {
        return $this->len;
    }

    public function delete(Hashable&GoValue $at): void
    {
        unset($this->values[$at->hash()]);
    }

    public function iter(): iterable
    {
        foreach ($this->values as ['key' => $key, 'value' => $value]) {
            /** @var K $key */
            $key = $key->copy();

            yield $key => $value;
        }
    }
}
