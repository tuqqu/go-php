<?php

declare(strict_types=1);

namespace GoPhp\GoValue\String;

use GoPhp\GoType\NamedType;

final class StringValue extends BaseString
{
    public function type(): NamedType
    {
        return NamedType::String;
    }
}
