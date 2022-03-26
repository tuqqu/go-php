<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Float;

use GoPhp\GoValue\SimpleNumber;

abstract class BaseFloatValue extends SimpleNumber
{
    public readonly float $value;

    public function __construct(float $value)
    {
        $this->value = $value;
    }

    public function unwrap(): float
    {
        return $this->value;
    }

    public function negate(): static
    {
        return new static(-$this->value);
    }

    public function noop(): static
    {
        return $this;
    }

    // binary

    public function add(self $value): static
    {
        return new static($this->value + $value->value);
    }

    public function sub(self $value): static
    {
        return new static($this->value - $value->value);
    }

    public function div(self $value): static
    {
        return new static($this->value / $value->value);
    }

    public function mod(self $value): static
    {
        return new static($this->value % $value->value);
    }

    public function mul(self $value): static
    {
        return new static($this->value * $value->value);
    }
}
