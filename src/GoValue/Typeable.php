<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\GoType\NamedType;

interface Typeable
{
    public function becomeTyped(NamedType $type): AddressableValue&Sealable;
}
