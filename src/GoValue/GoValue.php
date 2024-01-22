<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\GoType\GoType;
use GoPhp\Operator;

/**
 * @template T of mixed (the underlying PHP value)
 */
interface GoValue
{
    /**
     * Unary operation on a value.
     */
    public function operate(Operator $op): self;

    /**
     * Binary operation on two values. Value on the left hand side is $this.
     */
    public function operateOn(Operator $op, self $rhs): self;

    /**
     * Mutates the value on the left hand side with the value on the right hand side.
     */
    public function mutate(Operator $op, self $rhs): void;

    /**
     * Unwraps the value, returning the underlying PHP value.
     *
     * @return T
     */
    public function unwrap(): mixed;

    /**
     * Returns a new value that is a copy of the current value.
     */
    public function copy(): self;

    /**
     * Returns the type of the value.
     */
    public function type(): GoType;

    /**
     * Returns the string representation of the value.
     */
    public function toString(): string;
}
