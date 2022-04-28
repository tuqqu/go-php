<?php

declare(strict_types=1);

namespace GoPhp\Env\Builtin;

use GoPhp\Error\TypeError;
use GoPhp\GoType\NamedType;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Int\BaseIntValue;
use GoPhp\GoValue\Slice\SliceValue;
use GoPhp\GoValue\StringValue;

final class StringConverter
{
    private const INVALID_RANGE_CHAR = "\u{FFFD}";

    public static function convert(GoValue $value): StringValue
    {
        switch (true) {
            case $value instanceof StringValue:
                return $value;
            case $value instanceof BaseIntValue:
                return new StringValue(self::char($value));
            case $value instanceof SliceValue
                && ($value->type->internalType->equals(NamedType::Uint8)
                || $value->type->internalType->equals(NamedType::Int32)):
                    return new StringValue(\implode('', \array_map(self::char(...), $value->values)));
        }

        throw TypeError::conversionError($value, NamedType::String);
    }

    private static function char(BaseIntValue $value): string
    {
        $int = $value->unwrap();

        $char = \mb_chr($int, 'UTF-8');

        return $char === false ?
            self::INVALID_RANGE_CHAR :
            $char;
    }
}
