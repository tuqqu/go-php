<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

trait NamedTrait
{
    private bool $named = false;

    public function makeNamed(): void
    {
        $this->named = true;
    }

    public function isNamed(): bool
    {
        return $this->named;
    }
}
