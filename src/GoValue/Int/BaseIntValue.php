<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Int;

use GoPhp\GoValue\Addable;
use GoPhp\GoValue\Number;
use GoPhp\GoValue\SimpleNumber;

abstract class BaseIntValue extends SimpleNumber
{
    public const MIN = PHP_INT_MIN;
    public const MAX = PHP_INT_MAX;

    public readonly int $value;

    public function __construct(int $value)
    {
        self::assertInBounds($value);
        $this->value = $value;
    }

    public function unwrap(): int
    {
        return $this->value;
    }

    // arith

    public function negate(): static
    {
        return self::newWithWrap(-$this->value);
    }

    public function noop(): static
    {
        return $this;
    }

    // binary

    public function add(Addable $value): static
    {
        return self::newWithWrap($this->value + $value->value);
    }

    public function sub(Number $value): static
    {
        return self::newWithWrap($this->value - $value->value);
    }

    public function div(Number $value): static
    {
        return self::newWithWrap($this->value / $value->value);
    }

    public function mod(Number $value): static
    {
        return self::newWithWrap($this->value % $value->value);
    }

    public function mul(Number $value): static
    {
        return self::newWithWrap($this->value * $value->value);
    }

    final protected static function newWithWrap(int|float $value): static
    {
        return new static(self::wrap($value));
    }

    final protected static function assertInBounds(int|float $value): void
    {
        if (self::wrap($value) !== $value) {
            throw new \Exception('outofbounds');
        }
    }

    final protected static function wrap(int|float $value): int|float
    {
        return match (true) {
            $value < static::MIN => self::wrap(static::MAX + $value),
            $value > static::MAX => self::wrap(-static::MAX + $value),
            default => $value,
        };
    }
}
