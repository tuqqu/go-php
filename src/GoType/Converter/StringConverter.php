<?php

declare(strict_types=1);

namespace GoPhp\GoType\Converter;

use GoPhp\Error\RuntimeError;
use GoPhp\GoType\NamedType;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Int\IntNumber;
use GoPhp\GoValue\Slice\SliceValue;
use GoPhp\GoValue\String\BaseString;
use GoPhp\GoValue\String\UntypedStringValue;

use function array_map;
use function implode;
use function mb_chr;

final class StringConverter
{
    private const INVALID_RANGE_CHAR = "\u{FFFD}";

    public static function convert(GoValue $value): BaseString
    {
        return match (true) {
            $value instanceof BaseString => $value,
            $value instanceof IntNumber => new UntypedStringValue(self::char($value)),
            $value instanceof SliceValue
            && self::isSliceConvertible($value) => new UntypedStringValue(self::chars($value->unwrap())),
            default => throw RuntimeError::conversionError($value, NamedType::String),
        };
    }

    private static function char(IntNumber $value): string
    {
        $int = $value->unwrap();

        $char = mb_chr($int, 'UTF-8');

        return $char === false
            ? self::INVALID_RANGE_CHAR
            : $char;
    }

    private static function chars(array $values): string
    {
        return implode('', array_map(self::char(...), $values));
    }

    private static function isSliceConvertible(SliceValue $slice): bool
    {
        return $slice->type->elemType === NamedType::Byte
            || $slice->type->elemType === NamedType::Rune;
    }
}
