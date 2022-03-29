<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Int;

use GoPhp\GoType\BasicType;

final class Int32Value extends BaseIntValue
{
    public const MIN = -2_147_483_648;
    public const MAX = +2_147_483_647;

    public static function fromRune(string $rune): self
    {
        return new self(\mb_ord(\trim($rune, '\''), 'UTF-8'));
    }

    public function type(): BasicType
    {
        return BasicType::Int32;
    }
}