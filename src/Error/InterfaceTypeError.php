<?php

declare(strict_types=1);

namespace GoPhp\Error;

use GoPhp\GoType\InterfaceType;
use GoPhp\GoType\WrappedType;
use GoPhp\GoValue\WrappedValue;

use function sprintf;

class InterfaceTypeError extends RuntimeError
{
    private WrappedValue|WrappedType|null $value = null;
    private ?string $missingMethod = null;

    public static function cannotUseAsInterfaceType(WrappedValue|WrappedType $value, InterfaceType $interfaceType): self
    {
        $missingMethod = (string) $interfaceType->tryGetMissingMethod(
            $value instanceof WrappedType
                ? $value
                : $value->type(),
        );

        $error = self::cannotUseAsType($value, $interfaceType, $missingMethod);

        $error->value = $value;
        $error->missingMethod = $missingMethod;

        return $error;
    }

    public static function fromOther(self $error, WrappedType $interfaceType): self
    {
        if (!isset($error->value, $error->missingMethod)) {
            throw InternalError::unreachable('cannot convert error from other error');
        }

        return self::cannotUseAsType(
            $error->value,
            $interfaceType,
            $error->missingMethod,
        );
    }

    private static function cannotUseAsType(
        WrappedValue|WrappedType $value,
        InterfaceType|WrappedType $interfaceType,
        string $missingMethod,
    ): self {
        return new self(
            sprintf(
                "cannot use %s (value of type %s) as type %s:\n\t%s does not implement %s (missing %s method)",
                $value instanceof WrappedType
                    ? $value->name()
                    : self::valueToString($value),
                $value instanceof WrappedType
                    ? $value->name()
                    : $value->type()->name(),
                $interfaceType->name(),
                $value instanceof WrappedType
                    ? $value->name()
                    : $value->type()->name(),
                $interfaceType->name(),
                $missingMethod,
            ),
        );
    }
}
