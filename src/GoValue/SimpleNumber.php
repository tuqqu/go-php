<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Error\RuntimeError;
use GoPhp\GoType\NamedType;
use GoPhp\GoType\UntypedType;
use GoPhp\GoValue\Complex\Complex128Value;
use GoPhp\GoValue\Complex\Complex64Value;
use GoPhp\GoValue\Complex\ComplexNumber;
use GoPhp\GoValue\Float\Float32Value;
use GoPhp\GoValue\Float\Float64Value;
use GoPhp\GoValue\Int\Int16Value;
use GoPhp\GoValue\Int\Int32Value;
use GoPhp\GoValue\Int\Int64Value;
use GoPhp\GoValue\Int\Int8Value;
use GoPhp\GoValue\Int\IntValue;
use GoPhp\GoValue\Int\Uint16Value;
use GoPhp\GoValue\Int\Uint32Value;
use GoPhp\GoValue\Int\Uint64Value;
use GoPhp\GoValue\Int\Uint8Value;
use GoPhp\GoValue\Int\UintptrValue;
use GoPhp\GoValue\Int\UintValue;
use GoPhp\Operator;

use function GoPhp\assert_values_compatible;
use function GoPhp\normalize_unwindable;

/**
 * @template N = int|float
 *
 * @template-implements Hashable<N>
 * @template-implements AddressableValue<N>
 */
abstract class SimpleNumber implements Hashable, Castable, Sealable, Typeable, AddressableValue
{
    use TypeableTrait;
    use SealableTrait;
    use AddressableTrait;

    // fixme move this to child classes
    final public function convertTo(NamedType $type): self|ComplexNumber
    {
        $number = $this->unwrap();

        return match ($type) {
            NamedType::Int => new IntValue((int) $number),
            NamedType::Int8 => new Int8Value((int) $number),
            NamedType::Int16 => new Int16Value((int) $number),
            NamedType::Int32 => new Int32Value((int) $number),
            NamedType::Int64 => new Int64Value((int) $number),
            NamedType::Uint => new UintValue((int) $number),
            NamedType::Uint8 => new Uint8Value((int) $number),
            NamedType::Uint16 => new Uint16Value((int) $number),
            NamedType::Uint32 => new Uint32Value((int) $number),
            NamedType::Uint64 => new Uint64Value((int) $number),
            NamedType::Uintptr => new UintptrValue((int) $number),
            NamedType::Float32 => new Float32Value((float) $number),
            NamedType::Float64 => new Float64Value((float) $number),
            NamedType::Complex64 => Complex64Value::fromSimpleNumber($this),
            NamedType::Complex128 => Complex128Value::fromSimpleNumber($this),
            default => throw RuntimeError::implicitConversionError($this, $type),
        };
    }

    public function toString(): string
    {
        return (string) $this->unwrap();
    }

    final public function operate(Operator $op): self|PointerValue
    {
        return match ($op) {
            Operator::Plus => $this->noop(),
            Operator::Minus => $this->negate(),
            Operator::BitAnd => $this->isAddressable()
                ? PointerValue::fromValue($this)
                : throw RuntimeError::cannotTakeAddressOfValue($this),
            default => static::completeOperate($op),
        };
    }

    final public function operateOn(Operator $op, GoValue $rhs): self|ComplexNumber|BoolValue
    {
        assert_values_compatible($this, $rhs);

        $rhs = normalize_unwindable($rhs);

        if ($rhs instanceof ComplexNumber && $this->type() instanceof UntypedType) {
            return $rhs::fromSimpleNumber($this)->operateOn($op, $rhs);
        }

        return match ($op) {
            Operator::Plus => $this->add($rhs),
            Operator::Minus => $this->sub($rhs),
            Operator::Mul => $this->mul($rhs),
            Operator::Div => $this->div($rhs),
            Operator::Mod => $this->mod($rhs),
            Operator::EqEq => $this->equals($rhs),
            Operator::NotEq => $this->equals($rhs)->invert(),
            Operator::Greater => $this->greater($rhs),
            Operator::GreaterEq => $this->greaterEq($rhs),
            Operator::Less => $this->less($rhs),
            Operator::LessEq => $this->lessEq($rhs),
            default => static::completeOperateOn($op, $rhs),
        };
    }

    final public function mutate(Operator $op, GoValue $rhs): void
    {
        $this->onMutate();

        assert_values_compatible($this, $rhs);

        $rhs = normalize_unwindable($rhs);

        match ($op) {
            Operator::Eq => $this->assign($rhs),
            Operator::PlusEq, Operator::Inc => $this->mutAdd($rhs),
            Operator::MinusEq, Operator::Dec => $this->mutSub($rhs),
            Operator::MulEq => $this->mutMul($rhs),
            Operator::DivEq => $this->mutDiv($rhs),
            Operator::ModEq => $this->mutMod($rhs),
            default => static::completeMutate($op, $rhs),
        };
    }

    final public function hash(): int
    {
        return $this->unwrap();
    }

    public function cast(NamedType $to): self|ComplexNumber
    {
        if ($this->type() instanceof UntypedType) {
            return $this->convertTo($to);
        }

        return $this;
    }

    public function copy(): static
    {
        return clone $this;
    }

    public function equals(self $rhs): BoolValue
    {
        return new BoolValue($this->unwrap() === $rhs->unwrap());
    }

    public function greater(self $other): BoolValue
    {
        return new BoolValue($this->unwrap() > $other->unwrap());
    }

    public function greaterEq(self $other): BoolValue
    {
        return new BoolValue($this->unwrap() >= $other->unwrap());
    }

    public function less(self $other): BoolValue
    {
        return new BoolValue($this->unwrap() < $other->unwrap());
    }

    public function lessEq(self $other): BoolValue
    {
        return new BoolValue($this->unwrap() <= $other->unwrap());
    }

    final protected function noop(): static
    {
        return $this;
    }

    abstract protected function negate(): static;

    abstract protected function add(self $value): static;

    abstract protected function sub(self $value): static;

    abstract protected function div(self $value): static;

    abstract protected function mod(self $value): static;

    abstract protected function mul(self $value): static;

    abstract protected function mutAdd(self $value): void;

    abstract protected function mutSub(self $value): void;

    abstract protected function mutDiv(self $value): void;

    abstract protected function mutMod(self $value): void;

    abstract protected function mutMul(self $value): void;

    abstract protected function assign(self $value): void;

    protected function completeOperate(Operator $op): static|PointerValue
    {
        throw RuntimeError::undefinedOperator($op, $this, true);
    }

    protected function completeOperateOn(Operator $op, GoValue $rhs): static
    {
        throw RuntimeError::undefinedOperator($op, $this);
    }

    protected function completeMutate(Operator $op, GoValue $rhs): void
    {
        throw RuntimeError::undefinedOperator($op, $this);
    }
}
