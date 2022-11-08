<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

/**
 * @template H = string|int|float|bool|null
 */
interface Hashable
{
    /**
     * @return H
     */
    public function hash(): string|int|float|bool|null;
}
