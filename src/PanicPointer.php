<?php

declare(strict_types=1);

namespace GoPhp;

use GoPhp\Error\PanicError;

final class PanicPointer
{
    private ?PanicError $panic = null;

    public function set(PanicError $panic): void
    {
        $this->panic = $panic;
    }

    public function clear(): void
    {
        $this->panic = null;
    }

    public function pointsTo(): ?PanicError
    {
        return $this->panic;
    }
}
