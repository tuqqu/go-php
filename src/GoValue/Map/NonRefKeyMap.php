<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Map;

use GoPhp\GoValue\GoValue;

final class NonRefKeyMap implements Map
{
    private array $values = [];
    private int $len = 0;

    public function has(GoValue $at): bool
    {
        return isset($this->values[$at->unwrap()]);
    }

    public function get(GoValue $at): GoValue
    {
        return $this->values[$at->unwrap()];
    }

    public function set(GoValue $value, GoValue $at): void
    {
        if (!$this->has($at)) {
            ++$this->len;
        }

        $this->values[$at->unwrap()] = $value;
    }

    public function len(): int
    {
        return $this->len;
    }

    /**
     * @return iterable<mixed, GoValue>
     */
    public function iter(): iterable
    {
        yield from $this->values;
    }
}
