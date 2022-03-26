<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Float;

use GoPhp\GoValue\Addable;
use GoPhp\GoValue\Number;
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

    public function add(Addable $value): static
    {
        return new static($this->value + $value->value);
    }

    public function sub(Number $value): static
    {
        return new static($this->value - $value->value);
    }

    public function div(Number $value): static
    {
        return new static($this->value / $value->value);
    }

    public function mod(Number $value): static
    {
        return new static($this->value % $value->value);
    }

    public function mul(Number $value): static
    {
        return new static($this->value * $value->value);
    }
}
