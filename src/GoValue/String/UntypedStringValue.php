<?php

declare(strict_types=1);

namespace GoPhp\GoValue\String;

use GoPhp\GoType\UntypedType;

final class UntypedStringValue extends BaseString
{
    public function type(): UntypedType
    {
        return UntypedType::UntypedString;
    }
}
