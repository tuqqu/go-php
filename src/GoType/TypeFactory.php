<?php

declare(strict_types=1);

namespace GoPhp\GoType;

final class TypeFactory
{
    public static function tryFrom(mixed $value): ?ValueType
    {
        if (\is_string($value)) {
            return BasicType::tryFrom($value);
        }

        return null;
    }
}
