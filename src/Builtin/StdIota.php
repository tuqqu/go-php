<?php

declare(strict_types=1);

namespace GoPhp\Builtin;

use GoPhp\GoType\GoType;
use GoPhp\GoType\UntypedType;
use GoPhp\GoValue\Int\IntNumber;

class StdIota extends IntNumber implements Iota
{
    public function type(): GoType
    {
        return UntypedType::UntypedInt;
    }

    public function set(int $value): void
    {
        $this->value = $value;
    }
}
