<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

/**
 * Value gets sealed when a constant is assigned to it.
 */
interface Sealable
{
    public function seal(): void;
}
