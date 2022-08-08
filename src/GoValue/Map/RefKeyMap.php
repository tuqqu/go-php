<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Map;

use GoPhp\GoValue\GoValue;

/**
 * @template K of GoValue
 * @template V of GoValue
 * @template-implements Map<K, V>
 */
final class RefKeyMap implements Map
{
    private \SplObjectStorage $values;
    private int $len;

    public function __construct()
    {
        $this->values = new \SplObjectStorage();
        $this->len = 0;
    }

    public function has(GoValue $at): bool
    {
        return isset($this->values[$at]);
    }

    public function get(GoValue $at): GoValue
    {
        return $this->values[$at];
    }

    public function set(GoValue $value, GoValue $at): void
    {
        if (!$this->has($at)) {
            ++$this->len;
        }

        $this->values->attach($at, $value);
    }

    public function len(): int
    {
        return $this->len;
    }

    public function delete(GoValue $at): void
    {
        $this->values->detach($at);
    }

    public function iter(): iterable
    {
        for ($i = 0; $i < $this->len; ++$i) {
            $key = $this->values->current();
            $value = $this->values->getInfo();

            $this->values->next();

            yield $key => $value;
        }

        $this->values->rewind();
    }
}
