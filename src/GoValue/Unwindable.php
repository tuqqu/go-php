<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

/**
 * @template T of object
 */
interface Unwindable
{
    /**
     * @return T
     */
    public function unwind(): object;
}
