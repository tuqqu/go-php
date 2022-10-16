<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Complex;

use GoPhp\Error\InternalError;
use GoPhp\Error\OperationError;
use GoPhp\Error\TypeError;
use GoPhp\GoType\BasicType;
use GoPhp\GoType\GoType;
use GoPhp\GoType\UntypedType;
use GoPhp\GoValue\AddressableTrait;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\Float\BaseFloatValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\NonRefValue;
use GoPhp\GoValue\PointerValue;
use GoPhp\GoValue\Sealable;
use GoPhp\GoValue\SealableTrait;
use GoPhp\GoValue\SimpleNumber;
use GoPhp\Operator;

use function GoPhp\assert_values_compatible;
use function GoPhp\float_type_from_complex;
use function GoPhp\normalize_value;

abstract class BaseComplexValue implements NonRefValue, Sealable, AddressableValue
{
    use SealableTrait;
    use AddressableTrait;

    public const NAME = 'complex';

    public static function create(mixed $value): NonRefValue
    {
        // fixme check
        throw new InternalError();
    }

    public static function fromSimpleNumber(SimpleNumber $number): static
    {
        return new static((float) $number->unwrap(), 0.0);
    }

    public function __construct(
        protected float $real,
        protected float $imag,
    ) {}

    public function unwrap(): mixed
    {
        // fixme check
        throw new InternalError();
    }

    final public function reify(?GoType $with = null): self
    {
//        if ($this->type() instanceof UntypedType) {
//            return $this->convertTo($with);
//        }

        return $this;
    }

    public function toString(): string
    {
        return \sprintf(
            '(%s%f%s%f)',
            $this->real >= 0 ? '+' : '',
            $this->real,
            $this->imag >= 0 ? '+' : '',
            $this->imag,
        );
    }

    final public function operate(Operator $op): self|PointerValue
    {
        return match ($op) {
            Operator::BitAnd => $this->isAddressable()
                ? PointerValue::fromValue($this)
                : throw TypeError::cannotTakeAddressOfValue($this),
            Operator::Plus => $this->noop(),
            Operator::Minus => $this->negate(),
            default => throw OperationError::undefinedOperator($op, $this, true),
        };
    }

    final public function operateOn(Operator $op, GoValue $rhs): NonRefValue
    {
        assert_values_compatible($this, $rhs);

        $rhs = normalize_value($rhs);

        if ($rhs instanceof SimpleNumber && $rhs->type() instanceof UntypedType) {
            $rhs = static::fromSimpleNumber($rhs);
        }

        return match ($op) {
            Operator::Plus => $this->add($rhs),
            Operator::Minus => $this->sub($rhs),
            Operator::Mul => $this->mul($rhs),
            Operator::Div => $this->div($rhs),
            Operator::EqEq => $this->equals($rhs),
            Operator::NotEq => $this->equals($rhs)->invert(),
            default => throw OperationError::undefinedOperator($op, $this),
        };
    }

    final public function mutate(Operator $op, GoValue $rhs): void
    {
        $this->onMutate();

        assert_values_compatible($this, $rhs);

        $rhs = normalize_value($rhs);

        if ($rhs instanceof SimpleNumber && $rhs->type() instanceof UntypedType) {
            $rhs = static::fromSimpleNumber($rhs);
        }

        match ($op) {
            Operator::Eq => $this->assign($rhs),
            Operator::PlusEq, Operator::Inc => $this->mutAdd($rhs),
            Operator::MinusEq, Operator::Dec => $this->mutSub($rhs),
            Operator::MulEq => $this->mutMul($rhs),
            Operator::DivEq => $this->mutDiv($rhs),
            default => throw OperationError::undefinedOperator($op, $this),
        };
    }

    public function copy(): static
    {
        //fixme check
        return clone $this;
    }

    public function equals(self $rhs): BoolValue
    {
        return new BoolValue($this->real === $rhs->real && $this->imag === $rhs->imag);
    }

    final public function real(): BaseFloatValue
    {
        return $this->createFloat($this->real);
    }

    final public function imag(): BaseFloatValue
    {
        return $this->createFloat($this->imag);
    }

    abstract public function type(): BasicType;

    /**
     * @psalm-suppress
     */
    final protected function createFloat(float $float): BaseFloatValue
    {
        return float_type_from_complex($this->type())->defaultValue()::create($float);
    }

    final protected function noop(): static
    {
        return $this;
    }

    final protected function negate(): static
    {
        return new static(-$this->real, -$this->imag);
    }

    final protected function add(self $value): static
    {
        return new static($this->real + $value->real, $this->imag + $value->imag);
    }

    final protected function sub(self $value): static
    {
        return new static($this->real - $value->real, $this->imag - $value->imag);
    }

    final protected function div(self $value): static
    {
        $a = $value->real**2 + $value->imag**2;
        $b = ($this->real * $value->real + $this->imag * $value->imag) / $a;
        $c = ($this->imag * $value->real - $this->real * $value->imag) / $a;

        return new static($b, $c);
    }

    final protected function mul(self $value): static
    {
        $a = $this->real * $value->real - $this->imag * $value->imag;
        $b = $this->real * $value->imag + $this->imag * $value->real;

        return new static($a, $b);
    }

    final protected function mutAdd(self $value): void
    {
        $this->real += $value->real;
        $this->imag += $value->imag;
    }

    final protected function mutSub(self $value): void
    {
        $this->real -= $value->real;
        $this->imag -= $value->imag;
    }

    final protected function mutDiv(self $value): void
    {
        $this->real /= $value->real;
        $this->imag /= $value->imag;
    }

    final protected function mutMul(self $value): void
    {
        $this->real *= $value->real;
        $this->imag *= $value->imag;
    }

    final protected function assign(self $value): void
    {
        $this->real = $value->real;
        $this->imag = $value->imag;
    }
}
