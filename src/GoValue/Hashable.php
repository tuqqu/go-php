<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

/**
 * @template H = scalar
 */
interface Hashable
{
    /**
     * @return H
     */
    public function hash(): string|int|float|bool;
}
