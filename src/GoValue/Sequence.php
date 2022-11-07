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
    /**
     * Length of the sequence.
     */
    public function len(): int;

    /**
     * Get the value at the given index.
     *
     * @param K $at
     * @return V
     */
    public function get(GoValue $at): GoValue;

    /**
     * Iterate over the sequence. Note that the key is not necessarily an integer.
     *
     * @return iterable<K, V>
     */
    public function iter(): iterable;
}
