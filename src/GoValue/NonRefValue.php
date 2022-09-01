<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\GoType\GoType;

interface NonRefValue extends GoValue
{
    public static function create(mixed $value): self;

    // fixme revisit arg
    public function reify(?GoType $with = null): self;
}
