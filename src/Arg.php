<?php

declare(strict_types=1);

namespace GoPhp;

use GoPhp\GoValue\AddressableValue;

/**
 * @template V
 */
final class Arg
{
    /**
     * @param V $value
     */
    public function __construct(
        public readonly int $pos,
        public readonly AddressableValue $value,
    ) {}
}
