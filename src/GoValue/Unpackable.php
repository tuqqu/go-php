<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

/**
 * Value that can be unpacked with "..." when passed as an argument
 *
 * @template V of GoValue
 */
interface Unpackable
{
    /**
     * @return iterable<V>
     */
    public function unpack(): iterable;
}
