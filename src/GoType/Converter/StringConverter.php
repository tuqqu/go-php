<?php

declare(strict_types=1);

namespace GoPhp\GoType\Converter;

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
        return match (true) {
            $value instanceof StringValue => $value,
            $value instanceof BaseIntValue => new StringValue(self::char($value)),
            $value instanceof SliceValue
            && self::isSliceConvertible($value) => new StringValue(self::chars($value->unwrap())),
            default => throw TypeError::conversionError($value, NamedType::String),
        };
    }

    private static function char(BaseIntValue $value): string
    {
        $int = $value->unwrap();

        $char = \mb_chr($int, 'UTF-8');

        return $char === false
            ? self::INVALID_RANGE_CHAR
            : $char;
    }

    private static function chars(array $values): string
    {
        return \implode('', \array_map(self::char(...), $values));
    }

    private static function isSliceConvertible(SliceValue $slice): bool
    {
        return $slice->type->elemType === NamedType::Byte
            || $slice->type->elemType === NamedType::Rune;
    }
}
