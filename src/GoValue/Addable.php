<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

interface Addable extends GoValue
{
    public function add(self $value): self;
}
