<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Int;

use GoPhp\GoType\NamedType;

final class Int16Value extends IntNumber
{
    public const MIN = -32_768;
    public const MAX = +32_767;

    public function type(): NamedType
    {
        return NamedType::Int16;
    }
}
