<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

interface Number extends Addable
{
    public function noop(): static;

    public function negate(): static;

    public function sub(self $value): static;

    public function div(self $value): static;

    public function mod(self $value): static;

    public function mul(self $value): static;
}
