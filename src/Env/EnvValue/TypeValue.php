<?php

declare(strict_types=1);

namespace GoPhp\Env\EnvValue;

use GoPhp\GoType\GoType;
use GoPhp\GoValue\GoValue;

final class TypeValue extends EnvValue
{
    public function __construct(
        public readonly string $name,
        public readonly GoType $type,
        public GoValue $value,
    ) {}
}
