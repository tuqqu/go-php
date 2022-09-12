<?php

declare(strict_types=1);

namespace GoPhp\Builtin;

use GoPhp\GoType\GoType;
use GoPhp\GoType\UntypedType;
use GoPhp\GoValue\Int\BaseIntValue;

class StdIota extends BaseIntValue implements Iota
{
    public function __construct(int $value = 0)
    {
        parent::__construct($value);
    }

    public function type(): GoType
    {
        return UntypedType::UntypedInt;
    }

    public function set(int $value): void
    {
        $this->value = $value;
    }
};
