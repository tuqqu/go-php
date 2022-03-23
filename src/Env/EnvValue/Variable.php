<?php

declare(strict_types=1);

namespace GoPhp\Env\EnvValue;

use GoPhp\GoValue\GoValue;

final class Variable extends EnvValue
{
    public function set(GoValue $value): void
    {
        $this->value = $this->getType()->conforms($value->type()) ?
            $value :
            throw new \Exception('Type error'. $value->type()->name() . ' ' . $this->getType()->name());
    }
}
