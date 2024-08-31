<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Map;

use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Hashable;
use GoPhp\GoValue\Sequence;

/**
 * @template K of GoValue&Hashable
 * @template V of GoValue
 *
 * @template-extends Sequence<K, V>
 */
interface Map extends Sequence
{
    /**
     * @param K $at
     */
    public function has(GoValue&Hashable $at): bool;

    /**
     * @param K $at
     */
    public function delete(GoValue&Hashable $at): void;

    /**
     * @param V $value
     * @param K $at
     */
    public function set(GoValue $value, GoValue&Hashable $at): void;
}
