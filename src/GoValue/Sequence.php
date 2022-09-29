<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

/**
 * Value that consists of a sequence (ordered or unordered) of other values.
 * On a sequence an indexing operation can be performed, e.g. x[1]
 *
 * @template K of GoValue
 * @template V of GoValue
 */
interface Sequence
{
    public function len(): int;

    /**
     * @param K $at
     * @return V
     */
    public function get(GoValue $at): GoValue;

    /**
     * @return iterable<K, V>
     */
    public function iter(): iterable;
}
