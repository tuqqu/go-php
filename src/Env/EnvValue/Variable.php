<?php

declare(strict_types=1);

namespace GoPhp\Env\EnvValue;

use GoPhp\GoValue\GoValue;

final class Variable extends EnvValue
{
    private function set(GoValue $value): void
    {
        $this->value = $value;
    }
}
