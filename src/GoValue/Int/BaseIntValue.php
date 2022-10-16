<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Int;

use GoPhp\Error\TypeError;
use GoPhp\GoType\NamedType;
use GoPhp\GoType\UntypedType;
use GoPhp\GoValue\Complex\BaseComplexValue;
use GoPhp\GoValue\Complex\Complex128Value;
use GoPhp\GoValue\Complex\Complex64Value;
use GoPhp\GoValue\PointerValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\NonRefValue;
use GoPhp\GoValue\SimpleNumber;
use GoPhp\Operator;

/**
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

    protected function completeOperate(Operator $op): self|PointerValue
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
        return self::newWithWrap($this->value + $value->unwrap());
    }

    final protected function sub(parent $value): static
    {
        return self::newWithWrap($this->value - $value->unwrap());
    }

    final protected function div(parent $value): static
    {
        return self::newWithWrap((int) ($this->value / $value->unwrap()));
    }

    final protected function mod(parent $value): static
    {
        return self::newWithWrap($this->value % $value->unwrap());
    }

    final protected function mul(parent $value): static
    {
        return self::newWithWrap($this->value * $value->unwrap());
    }

    final protected function mutAdd(parent $value): void
    {
        $this->value = self::wrap($this->value + $value->unwrap());
    }

    final protected function mutSub(parent $value): void
    {
        $this->value = self::wrap($this->value - $value->unwrap());
    }

    final protected function mutDiv(parent $value): void
    {
        $this->value = self::wrap((int) ($this->value / $value->unwrap()));
    }

    final protected function mutMod(parent $value): void
    {
        $this->value = self::wrap($this->value % $value->unwrap());
    }

    final protected function mutMul(parent $value): void
    {
        $this->value = self::wrap($this->value * $value->unwrap());
    }

    final protected function assign(parent $value): void
    {
        $this->value = $value->unwrap();
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

    final protected function doBecomeTyped(NamedType $type): self|BaseComplexValue
    {
        if (!$this->type() instanceof UntypedType) {
            throw TypeError::implicitConversionError($this, $type);
        }

        return match ($type) {
            NamedType::Int => new IntValue($this->value),
            NamedType::Int8 => new Int8Value($this->value),
            NamedType::Int16 => new Int16Value($this->value),
            NamedType::Int32 => new Int32Value($this->value),
            NamedType::Int64 => new Int64Value($this->value),
            NamedType::Uint => new UintValue($this->value),
            NamedType::Uint8 => new Uint8Value($this->value),
            NamedType::Uint16 => new Uint16Value($this->value),
            NamedType::Uint32 => new Uint32Value($this->value),
            NamedType::Uint64 => new Uint64Value($this->value),
            NamedType::Uintptr => new UintptrValue($this->value),
            NamedType::Complex64 => Complex64Value::fromSimpleNumber($this),
            NamedType::Complex128 => Complex128Value::fromSimpleNumber($this),
            default => throw TypeError::implicitConversionError($this, $type),
        };
    }
}
