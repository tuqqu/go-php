<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Int;

use GoPhp\GoType\UntypedType;

final class UntypedIntValue extends BaseIntValue
{
    public static function fromString(string $digits): self
    {
        return new self((int) $digits);
    }

    public function type(): UntypedType
    {
        return UntypedType::UntypedInt;
    }
}
