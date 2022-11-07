<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

/**
 * Value that can be assigned to a constant, upon which it becomes selaed.
 */
interface Sealable
{
    public function seal(): void;

    public function isSealed(): bool;
}
