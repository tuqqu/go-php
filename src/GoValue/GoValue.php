<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Operator;
use GoPhp\GoType\ValueType;

interface GoValue
{
    public function operate(Operator $op): self;

    public function operateOn(Operator $op, self $rhs): self;

    public function equals(self $rhs): BoolValue;

    public function unwrap(): mixed;

    public function type(): ValueType;
}
