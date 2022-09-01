<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Error\OperationError;
use GoPhp\Error\TypeError;
use GoPhp\GoType\GoType;
use GoPhp\GoType\NamedType;
use GoPhp\GoType\UntypedType;
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
use function GoPhp\normalize_value;

/**
 * @template N of self
 */
abstract class SimpleNumber implements NonRefValue, Sealable, AddressableValue
{
    use SealableTrait;
    use AddressableTrait;

    public function __construct($value)
    {
        // intentionally left empty
    }

    final public static function create(mixed $value): static
    {
        return new static($value);
    }

    final public function reify(?GoType $with = null): self
    {
        if ($this->type() instanceof UntypedType) {
            return $this->convertTo($with);
        }

        return $this;
    }

    final public function convertTo(NamedType $type): self
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
            default => throw TypeError::implicitConversionError($this, $type),
        };
    }

    final public function becomeTyped(NamedType $type): self
    {
        $value = $this->doBecomeTyped($type);

        if ($this->isSealed()) {
            $value->seal();
        }

        if ($this->isAddressable()) {
            $value->makeAddressable();
        }

        return $value;
    }

    public function toString(): string
    {
        return (string) $this->unwrap();
    }

    abstract protected function doBecomeTyped(NamedType $type): self;

    final public function operate(Operator $op): self|AddressValue
    {
        return match ($op) {
            Operator::BitAnd => AddressValue::fromValue($this),
            Operator::Plus => $this->noop(),
            Operator::Minus => $this->negate(),
            default => static::completeOperate($op),
        };
    }

    final public function operateOn(Operator $op, GoValue $rhs): NonRefValue
    {
        assert_values_compatible($this, $rhs);

        $rhs = normalize_value($rhs);

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

        $rhs = normalize_value($rhs);

        match ($op) {
            Operator::Eq => $this->assign($rhs),
            Operator::PlusEq,
            Operator::Inc => $this->mutAdd($rhs),
            Operator::MinusEq,
            Operator::Dec => $this->mutSub($rhs),
            Operator::MulEq => $this->mutMul($rhs),
            Operator::DivEq => $this->mutDiv($rhs),
            Operator::ModEq => $this->mutMod($rhs),
            default => static::completeMutate($op, $rhs),
        };
    }

    public function copy(): static
    {
        return clone $this;
    }

    public function equals(GoValue $rhs): BoolValue
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

    /**
     * @param N $value
     */
    abstract protected function add(self $value): static;

    /**
     * @param N $value
     */
    abstract protected function sub(self $value): static;

    /**
     * @param N $value
     */
    abstract protected function div(self $value): static;

    /**
     * @param N $value
     */
    abstract protected function mod(self $value): static;

    /**
     * @param N $value
     */
    abstract protected function mul(self $value): static;

    /**
     * @param N $value
     */
    abstract protected function mutAdd(self $value): void;

    /**
     * @param N $value
     */
    abstract protected function mutSub(self $value): void;

    /**
     * @param N $value
     */
    abstract protected function mutDiv(self $value): void;

    /**
     * @param N $value
     */
    abstract protected function mutMod(self $value): void;

    /**
     * @param N $value
     */
    abstract protected function mutMul(self $value): void;

    /**
     * @param N $value
     */
    abstract protected function assign(self $value): void;

    protected function completeOperate(Operator $op): self|AddressValue
    {
        throw OperationError::undefinedOperator($op, $this, true);
    }

    protected function completeOperateOn(Operator $op, GoValue $rhs): NonRefValue
    {
        throw OperationError::undefinedOperator($op, $this);
    }

    protected function completeMutate(Operator $op, GoValue $rhs): void
    {
        throw OperationError::undefinedOperator($op, $this);
    }
}
