<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

interface Number extends Addable
{
    public function noop(): self;

    public function negate(): self;

    public function sub(self $value): self;

    public function div(self $value): self;

    public function mod(self $value): self;

    public function mul(self $value): self;
}
