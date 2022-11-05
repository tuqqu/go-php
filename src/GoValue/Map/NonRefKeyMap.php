<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Map;

use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\NonRefValue;

/**
 * @template K of NonRefValue
 * @template V of GoValue
 * @template-implements Map<K, V>
 */
final class NonRefKeyMap implements Map
{
    /** @var V[] */
    private array $values = [];
    private int $len = 0;

    /**
     * @param \Closure(mixed): K $wrapper
     */
    public function __construct(
        private readonly \Closure $wrapper,
    ) {}

    public function has(GoValue $at): bool
    {
        return isset($this->values[$at->hash()]);
    }

    public function get(GoValue $at): GoValue
    {
        return $this->values[$at->hash()];
    }

    public function set(GoValue $value, GoValue $at): void
    {
        if (!$this->has($at)) {
            ++$this->len;
        }

        $this->values[$at->hash()] = $value;
    }

    public function len(): int
    {
        return $this->len;
    }

    public function delete(GoValue $at): void
    {
        unset($this->values[$at->hash()]);
    }

    public function iter(): iterable
    {
        foreach ($this->values as $key => $value) {
            yield ($this->wrapper)($key) => $value;
        }
    }
}
