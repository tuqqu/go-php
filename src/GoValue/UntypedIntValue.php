<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

final class UntypedIntValue extends BaseIntValue
{
    public function type(): ValueType
    {
        // fixme should untyped types have its own type?
        return ValueType::Int;
    }
}
