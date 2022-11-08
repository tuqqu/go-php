<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Map;

use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Hashable;
use GoPhp\GoValue\Sequence;

/**
 * @template K of Hashable&GoValue
 * @template V of GoValue
 * @template-extends Sequence<K, V>
 */
interface Map extends Sequence
{
    /**
     * @param K $at
     */
    public function has(Hashable&GoValue $at): bool;

    /**
     * @param K $at
     */
    public function delete(Hashable&GoValue $at): void;

    /**
     * @param V $value
     * @param K $at
     */
    public function set(GoValue $value, Hashable&GoValue $at): void;
}
