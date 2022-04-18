<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Map;

use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\NonRefValue;

final class NonRefKeyMap implements Map
{
    /** @var GoValue[] */
    private array $values = [];
    private int $len = 0;

    /**
     * @param \Closure(mixed): NonRefValue
     */
    public function __construct(
        private \Closure $wrapper,
    ) {}

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
     * @return iterable<NonRefValue, GoValue>
     */
    public function iter(): iterable
    {
        foreach ($this->values as $key => $value) {
            yield ($this->wrapper)($key) => $value;
        }
    }
}
