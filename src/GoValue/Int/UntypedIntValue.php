<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Int;

use GoPhp\GoType\BasicType;

final class UntypedIntValue extends BaseIntValue
{
    public static function fromString(string $digits): self
    {
        return new self((int) $digits);
    }

    public function type(): BasicType
    {
        return BasicType::UntypedInt;
    }
}
