<?php

declare(strict_types=1);

namespace GoPhp\GoType;

final class TypeFactory
{
    public static function tryFrom(mixed $value): ?GoType
    {
        if (\is_string($value)) {
            return NamedType::tryFromName($value);
        }

        return null;
    }
}
