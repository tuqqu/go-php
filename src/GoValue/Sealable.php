<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

/**
 * Value gets sealed when it is assigned to a constant.
 */
interface Sealable
{
    public function seal(): void;

    public function isSealed(): bool;
}
