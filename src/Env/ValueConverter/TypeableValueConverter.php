<?php

declare(strict_types=1);

namespace GoPhp\Env\ValueConverter;

use GoPhp\GoType\GoType;
use GoPhp\GoType\NamedType;
use GoPhp\GoType\UntypedType;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Sealable;
use GoPhp\GoValue\Typeable;

final class TypeableValueConverter implements ValueConverter
{
    public function supports(GoValue $value, GoType $type): bool
    {
        return $value instanceof Typeable
            && $type instanceof NamedType
            && $value->type() instanceof UntypedType;
    }

    public function convert(GoValue $value, GoType $type): AddressableValue&Sealable
    {
        /**
         * @var Typeable $value
         * @var NamedType $type
         */
        return $value->becomeTyped($type);
    }
}
