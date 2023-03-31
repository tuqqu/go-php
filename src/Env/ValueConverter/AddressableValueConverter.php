<?php

declare(strict_types=1);

namespace GoPhp\Env\ValueConverter;

use GoPhp\GoType\GoType;
use GoPhp\GoType\WrappedType;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\TypeValue;

final class AddressableValueConverter implements ValueConverter
{
    public function supports(GoValue $value, GoType $type): bool
    {
        return $type instanceof WrappedType
            && $value instanceof AddressableValue
            && !$value instanceof TypeValue;
    }

    public function convert(GoValue $value, GoType $type): AddressableValue
    {
        /**
         * @var AddressableValue $value
         */
        return $type->convert($value);
    }
}
