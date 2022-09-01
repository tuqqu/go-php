<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Int;

use GoPhp\Error\TypeError;
use GoPhp\GoType\NamedType;
use GoPhp\GoType\UntypedType;
use GoPhp\GoValue\AddressValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\NonRefValue;
use GoPhp\GoValue\SimpleNumber;
use GoPhp\Operator;

/**
 * @template-extends SimpleNumber<self>
 * @psalm-suppress ImplementedParamTypeMismatch
 */
abstract class BaseIntValue extends SimpleNumber
{
    public const NAME = 'int';

    public const MIN = PHP_INT_MIN;
    public const MAX = PHP_INT_MAX;

    public int $value;

    public function __construct(int $value)
    {
        self::assertInBounds($value);
        $this->value = $value;
    }

    final public function unwrap(): int
    {
        return $this->value;
    }

    protected function completeOperate(Operator $op): self|AddressValue
    {
        return match ($op) {
            Operator::BitXor => $this->bitwiseNot(),
            default => parent::completeOperate($op),
        };
    }

    protected function completeOperateOn(Operator $op, GoValue $rhs): NonRefValue
    {
        /** @var self $rhs */
        return match ($op) {
            Operator::BitAndNot => $this->bitwiseAndNot($rhs),
            Operator::BitAnd => $this->bitwiseAnd($rhs),
            Operator::BitOr => $this->bitwiseOr($rhs),
            Operator::BitXor => $this->bitwiseXor($rhs),
            Operator::ShiftLeft => $this->bitwiseShiftLeft($rhs),
            Operator::ShiftRight => $this->bitwiseShiftRight($rhs),
            default => parent::completeOperateOn($op, $rhs),
        };
    }

    protected function completeMutate(Operator $op, GoValue $rhs): void
    {
        /** @var self $rhs */
        match ($op) {
            Operator::BitAndNotEq => $this->mutBitwiseAndNot($rhs),
            Operator::BitAndEq => $this->mutBitwiseAnd($rhs),
            Operator::BitOrEq => $this->mutBitwiseOr($rhs),
            Operator::BitXorEq => $this->mutBitwiseXor($rhs),
            Operator::ShiftLeftEq => $this->mutShiftLeft($rhs),
            Operator::ShiftRightEq => $this->mutShiftRight($rhs),
            default => parent::completeMutate($op, $rhs),
        };
    }

    // negation

    final protected function negate(): static
    {
        return self::newWithWrap(-$this->value);
    }

    // bitwise

    final protected function bitwiseNot(): static
    {
        return self::newWithWrap(~$this->value);
    }

    final protected function bitwiseAndNot(self $value): static
    {
        return self::newWithWrap($this->value & ~$value->value);
    }

    final protected function bitwiseAnd(self $value): static
    {
        return self::newWithWrap($this->value & $value->value);
    }

    final protected function bitwiseOr(self $value): static
    {
        return self::newWithWrap($this->value | $value->value);
    }

    final protected function bitwiseXor(self $value): static
    {
        return self::newWithWrap($this->value ^ $value->value);
    }

    final protected function bitwiseShiftLeft(self $value): static
    {
        return self::newWithWrap($this->value << $value->value);
    }

    final protected function bitwiseShiftRight(self $value): static
    {
        return self::newWithWrap($this->value >> $value->value);
    }

    // binary

    final protected function add(parent $value): static
    {
        return self::newWithWrap($this->value + $value->value);
    }

    final protected function sub(parent $value): static
    {
        return self::newWithWrap($this->value - $value->value);
    }

    final protected function div(parent $value): static
    {
        return self::newWithWrap((int) ($this->value / $value->value));
    }

    final protected function mod(parent $value): static
    {
        return self::newWithWrap($this->value % $value->value);
    }

    final protected function mul(parent $value): static
    {
        return self::newWithWrap($this->value * $value->value);
    }

    final protected function mutAdd(parent $value): void
    {
        $this->value = self::wrap($this->value + $value->value);
    }

    final protected function mutSub(parent $value): void
    {
        $this->value = self::wrap($this->value - $value->value);
    }

    final protected function mutDiv(parent $value): void
    {
        $this->value = self::wrap((int) ($this->value / $value->value));
    }

    final protected function mutMod(parent $value): void
    {
        $this->value = self::wrap($this->value % $value->value);
    }

    final protected function mutMul(parent $value): void
    {
        $this->value = self::wrap($this->value * $value->value);
    }

    final protected function assign(parent $value): void
    {
        $this->value = $value->value;
    }

    final protected function mutBitwiseAndNot(self $value): void
    {
        $this->value = self::wrap($this->value & ~$value->value);
    }

    final protected function mutBitwiseAnd(self $value): void
    {
        $this->value = self::wrap($this->value & $value->value);
    }

    final protected function mutBitwiseOr(self $value): void
    {
        $this->value = self::wrap($this->value | $value->value);
    }

    final protected function mutBitwiseXor(self $value): void
    {
        $this->value = self::wrap($this->value ^ $value->value);
    }

    final protected function mutShiftLeft(self $value): void
    {
        $this->value = self::wrap($this->value <<= $value->value);
    }

    final protected function mutShiftRight(self $value): void
    {
        $this->value = self::wrap($this->value >>= $value->value);
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

    final protected function doBecomeTyped(NamedType $type): self
    {
        if (!$this->type() instanceof UntypedType) {
            throw TypeError::implicitConversionError($this, $type);
        }

        $number = $this->unwrap();

        return match ($type) {
            NamedType::Int => new IntValue($number),
            NamedType::Int8 => new Int8Value($number),
            NamedType::Int16 => new Int16Value($number),
            NamedType::Int32 => new Int32Value($number),
            NamedType::Int64 => new Int64Value($number),
            NamedType::Uint => new UintValue($number),
            NamedType::Uint8 => new Uint8Value($number),
            NamedType::Uint16 => new Uint16Value($number),
            NamedType::Uint32 => new Uint32Value($number),
            NamedType::Uint64 => new Uint64Value($number),
            NamedType::Uintptr => new UintptrValue($number),
            default => throw TypeError::implicitConversionError($this, $type),
        };
    }
}
