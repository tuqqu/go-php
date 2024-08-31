<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Complex;

use GoPhp\Error\InternalError;
use GoPhp\Error\RuntimeError;
use GoPhp\GoType\BasicType;
use GoPhp\GoType\NamedType;
use GoPhp\GoType\UntypedType;
use GoPhp\GoValue\AddressableTrait;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\Castable;
use GoPhp\GoValue\Float\FloatNumber;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Hashable;
use GoPhp\GoValue\PointerValue;
use GoPhp\GoValue\Sealable;
use GoPhp\GoValue\SealableTrait;
use GoPhp\GoValue\SimpleNumber;
use GoPhp\Operator;

use function GoPhp\assert_values_compatible;
use function GoPhp\try_unwind;
use function sprintf;

/**
 * @psalm-type ComplexTuple = array{float, float}
 * @template-implements Hashable<int>
 * @template-implements AddressableValue<ComplexTuple>
 */
abstract class ComplexNumber implements Hashable, Castable, Sealable, AddressableValue
{
    use SealableTrait;
    use AddressableTrait;

    public const string NAME = 'complex';

    public static function fromSimpleNumber(SimpleNumber $number): static
    {
        return new static((float) $number->unwrap(), 0.0);
    }

    final public function __construct(
        protected float $real,
        protected float $imag,
    ) {}

    /**
     * @return ComplexTuple
     */
    public function unwrap(): array
    {
        return [$this->real, $this->imag];
    }

    public function toString(): string
    {
        return sprintf(
            '(%s%f%s%f)',
            $this->real >= 0 ? '+' : '',
            $this->real,
            $this->imag >= 0 ? '+' : '',
            $this->imag,
        );
    }

    final public function hash(): int
    {
        // Cantor Pairing Hash
        return (int) (100 * ($this->real + $this->imag) * ($this->real + $this->imag + 1) + $this->imag);
    }

    final public function cast(NamedType $to): self
    {
        return $this;
    }

    final public function operate(Operator $op): self|PointerValue
    {
        return match ($op) {
            Operator::BitAnd => $this->isAddressable()
                ? PointerValue::fromValue($this)
                : throw RuntimeError::cannotTakeAddressOfValue($this),
            Operator::Plus => $this->noop(),
            Operator::Minus => $this->negate(),
            default => throw RuntimeError::undefinedOperator($op, $this, true),
        };
    }

    final public function operateOn(Operator $op, GoValue $rhs): self|BoolValue
    {
        assert_values_compatible($this, $rhs);

        $rhs = try_unwind($rhs);

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
            default => throw RuntimeError::undefinedOperator($op, $this),
        };
    }

    final public function mutate(Operator $op, GoValue $rhs): void
    {
        $this->onMutate();

        assert_values_compatible($this, $rhs);

        $rhs = try_unwind($rhs);

        if ($rhs instanceof SimpleNumber && $rhs->type() instanceof UntypedType) {
            $rhs = static::fromSimpleNumber($rhs);
        }

        match ($op) {
            Operator::Eq => $this->assign($rhs),
            Operator::PlusEq, Operator::Inc => $this->mutAdd($rhs),
            Operator::MinusEq, Operator::Dec => $this->mutSub($rhs),
            Operator::MulEq => $this->mutMul($rhs),
            Operator::DivEq => $this->mutDiv($rhs),
            default => throw RuntimeError::undefinedOperator($op, $this),
        };
    }

    public function copy(): static
    {
        return clone $this;
    }

    public function equals(self $rhs): BoolValue
    {
        return new BoolValue($this->real === $rhs->real && $this->imag === $rhs->imag);
    }

    final public function real(): FloatNumber
    {
        return $this->createFloat($this->real);
    }

    final public function imag(): FloatNumber
    {
        return $this->createFloat($this->imag);
    }

    abstract public function type(): BasicType;

    final protected function createFloat(float $float): FloatNumber
    {
        $floatType = match ($this->type()) {
            NamedType::Complex64 => NamedType::Float32,
            NamedType::Complex128,
            UntypedType::UntypedComplex => NamedType::Float64,
            default => throw InternalError::unreachable($this),
        };

        /** @var FloatNumber $floatValue */
        $floatValue = $floatType->zeroValue();

        return new ($floatValue::class)($float);
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
        return new static(...self::computeForDiv($this, $value));
    }

    final protected function mul(self $value): static
    {
        return new static(...self::computeForMul($this, $value));
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
        [$real, $imag] = self::computeForDiv($this, $value);

        $this->real = $real;
        $this->imag = $imag;
    }

    final protected function mutMul(self $value): void
    {
        [$real, $imag] = self::computeForMul($this, $value);

        $this->real = $real;
        $this->imag = $imag;
    }

    final protected function assign(self $value): void
    {
        $this->real = $value->real;
        $this->imag = $value->imag;
    }

    /**
     * @return ComplexTuple
     */
    private static function computeForMul(self $lhs, self $rhs): array
    {
        $real = $lhs->real * $rhs->real - $lhs->imag * $rhs->imag;
        $imag = $lhs->real * $rhs->imag + $lhs->imag * $rhs->real;

        return [$real, $imag];
    }

    /**
     * @return ComplexTuple
     */
    private static function computeForDiv(self $lhs, self $rhs): array
    {
        $denominator = $rhs->real ** 2 + $rhs->imag ** 2;
        $real = ($lhs->real * $rhs->real + $lhs->imag * $rhs->imag) / $denominator;
        $imag = ($lhs->imag * $rhs->real - $lhs->real * $rhs->imag) / $denominator;

        return [$real, $imag];
    }
}
