<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Int;

use GoPhp\GoType\NamedType;

final class Uint8Value extends BaseIntValue
{
    public const MIN = 0;
    public const MAX = 255;

    public static function fromByte(string $byte): self
    {
        return new self(\mb_ord($byte));
    }

    public function type(): NamedType
    {
        return NamedType::Uint8;
    }
}
