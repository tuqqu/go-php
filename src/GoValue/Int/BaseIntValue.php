<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Int;

use GoPhp\GoValue\SimpleNumber;

abstract class BaseIntValue extends SimpleNumber
{
    public const MIN = PHP_INT_MIN;
    public const MAX = PHP_INT_MAX;

    public int $value;

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

    public function add(self $value): static
    {
        return self::newWithWrap($this->value + $value->value);
    }

    public function sub(self $value): static
    {
        return self::newWithWrap($this->value - $value->value);
    }

    public function div(self $value): static
    {
        return self::newWithWrap((int) ($this->value / $value->value));
    }

    public function mod(self $value): static
    {
        return self::newWithWrap($this->value % $value->value);
    }

    public function mul(self $value): static
    {
        return self::newWithWrap($this->value * $value->value);
    }

    public function mutAdd(self $value): void
    {
        $this->value = self::wrap($this->value + $value->value);
    }

    public function mutSub(self $value): void
    {
        $this->value = self::wrap($this->value - $value->value);
    }

    public function mutDiv(self $value): void
    {
        $this->value = self::wrap((int) ($this->value / $value->value));
    }

    public function mutMod(self $value): void
    {
        $this->value = self::wrap($this->value % $value->value);
    }

    public function mutMul(self $value): void
    {
        $this->value = self::wrap($this->value * $value->value);
    }

    final protected static function newWithWrap(int|float $value): static
    {
        return new static(self::wrap($value));
    }

    final protected static function assertInBounds(int|float $value): void
    {
        if ($value > static::MAX || $value < static::MIN) {
            throw new \Exception('outofbounds');
        }
    }

    final protected static function wrap(int|float $value): int|float
    {
        return match (true) {
            $value < static::MIN => self::wrap($value + static::MAX),
            $value > static::MAX => self::wrap($value - static::MAX),
            default => $value,
        };
    }
}
