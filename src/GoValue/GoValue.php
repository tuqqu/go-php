<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\GoType\GoType;
use GoPhp\Operator;

interface GoValue
{
    public function operate(Operator $op): self;

    public function operateOn(Operator $op, self $rhs): self;

    public function mutate(Operator $op, self $rhs): void;

    /**
     * Equality checks must be called only on values which are compatible.
     *
     * @template V of self
     * @param V $rhs
     */
    public function equals(self $rhs): BoolValue;

    public function unwrap(): mixed;

    public function copy(): self;

    public function type(): GoType;

    public function toString(): string;
}
