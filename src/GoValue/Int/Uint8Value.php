<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Int;

use GoPhp\GoType\NamedType;

use function mb_ord;

final class Uint8Value extends IntNumber
{
    public const int MIN = 0;
    public const int MAX = 255;

    public static function fromByte(string $byte): self
    {
        return new self(mb_ord($byte));
    }

    public function type(): NamedType
    {
        return NamedType::Uint8;
    }
}
