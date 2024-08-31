<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

/**
 * @psalm-type Hash = string|int
 * @template H = Hash
 */
interface Hashable
{
    /**
     * @return H
     */
    public function hash(): string|int;
}
