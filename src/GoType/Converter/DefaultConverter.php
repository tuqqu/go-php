<?php

declare(strict_types=1);

namespace GoPhp\GoType\Converter;

use GoPhp\Error\TypeError;
use GoPhp\GoType\GoType;
use GoPhp\GoValue\GoValue;

use function GoPhp\normalize_value;

final class DefaultConverter
{
    public static function convert(GoValue $value, GoType $type): GoValue
    {
        $value = normalize_value($value);

        return $type->equals($value->type()) ?
            $value :
            throw TypeError::conversionError($value, $value->type());
    }
}
