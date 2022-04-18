<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

interface NonRefValue extends GoValue
{
    public static function create(mixed $value): self;
}
