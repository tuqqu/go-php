<?php

declare(strict_types=1);

namespace GoPhp\Env\EnvValue;

use GoPhp\GoValue\GoValue;
use function GoPhp\assert_types_compatible;

final class MutableValue extends EnvValue
{
    public function set(GoValue $value): void
    {
        assert_types_compatible($this->getType(), $value->type());

        $this->value = self::convertIfNeeded($value, $this->type);
    }
}
