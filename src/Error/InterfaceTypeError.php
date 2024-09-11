<?php

declare(strict_types=1);

namespace GoPhp\Error;

use GoPhp\GoType\GoType;
use GoPhp\GoType\InterfaceType;
use GoPhp\GoValue\GoValue;

use function sprintf;

class InterfaceTypeError extends RuntimeError
{
    private GoValue|GoType|null $value = null;
    private ?string $missingMethod = null;

    public static function cannotUseAsInterfaceType(GoValue|GoType $value, InterfaceType $interfaceType): self
    {
        $missingMethod = (string) $interfaceType->tryGetMissingMethod(
            $value instanceof GoType
                ? $value
                : $value->type(),
        );

        $error = self::cannotUseAsType($value, $interfaceType, $missingMethod);
        $error->value = $value;
        $error->missingMethod = $missingMethod;

        return $error;
    }

    public static function fromOther(self $error, GoType $interfaceType): self
    {
        if (!isset($error->value, $error->missingMethod)) {
            throw InternalError::unreachable('cannot create interface error from another error');
        }

        return self::cannotUseAsType(
            $error->value,
            $interfaceType,
            $error->missingMethod,
        );
    }

    private static function cannotUseAsType(
        GoValue|GoType $value,
        InterfaceType|GoType $interfaceType,
        string $missingMethod,
    ): self {
        return new self(
            sprintf(
                'cannot use %s (value of type %s) as type %s: %s does not implement %s (missing method %s)',
                $value instanceof GoType
                    ? $value->name()
                    : self::valueToString($value),
                $value instanceof GoType
                    ? $value->name()
                    : $value->type()->name(),
                $interfaceType->name(),
                $value instanceof GoType
                    ? $value->name()
                    : $value->type()->name(),
                $interfaceType->name(),
                $missingMethod,
            ),
        );
    }
}
