<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

final class UntypedFloatValue extends BaseFloatValue
{
    // fixme check
    public function type(): ValueType
    {
        // fixme should untyped types have its own type?
        return ValueType::Float32;
    }
}
