<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Map;

use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Sequence;

/**
 * @template K of GoValue
 * @template V of GoValue
 * @template-extends Sequence<K, V>
 */
interface Map extends Sequence
{
    /**
     * @param K $at
     */
    public function has(GoValue $at): bool;

    /**
     * @param K $at
     */
    public function delete(GoValue $at): void;

    /**
     * @param V $value
     * @param K $at
     */
    public function set(GoValue $value, GoValue $at): void;
}
