<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\GoType\ValueType;
use GoPhp\Operator;

interface GoValue
{
    public function operate(Operator $op): self;

    public function operateOn(Operator $op, self $rhs): self;

    public function mutate(Operator $op, self $rhs): void;

    public function equals(self $rhs): BoolValue;

    public function unwrap(): mixed;

    public function type(): ValueType;

    public function toString(): string;
}
