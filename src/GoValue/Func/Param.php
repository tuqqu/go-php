<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Func;

use GoPhp\GoType\GoType;

final class Param
{
    public function __construct(
        public readonly GoType $type,
        public readonly ?string $name = null,
        public readonly bool $variadic = false,
    ) {}
}
