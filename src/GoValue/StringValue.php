<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Error\OperationError;
use GoPhp\GoType\NamedType;
use GoPhp\GoValue\Int\BaseIntValue;
use GoPhp\GoValue\Int\UntypedIntValue;
use GoPhp\Operator;
use function GoPhp\assert_index_exists;
use function GoPhp\assert_index_value;
use function GoPhp\assert_slice_indices;
use function GoPhp\assert_values_compatible;

final class StringValue implements Sliceable, Sequence, Constantable, NonRefValue
{
    use ConstantableTrait;

    public const NAME = 'string';

    private string $value;
    private int $byteLen;

    public function __construct(string $value)
    {
        $this->value = $value;
        $this->byteLen = \strlen($this->value);
    }

    public static function create(mixed $value): self
    {
        return new self($value);
    }

    public function reify(): NonRefValue
    {
        return $this;
    }

    public function type(): NamedType
    {
        return NamedType::String;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function slice(?int $low, ?int $high, ?int $max = null): self
    {
        if ($max !== null) {
            throw OperationError::cannotFullSliceString();
        }

        $low ??= 0;
        $high ??= $this->byteLen;

        assert_slice_indices($this->byteLen, $low, $high);

        return new self(\substr($this->value, $low, $high - $low));
    }

    public function operate(Operator $op): AddressValue
    {
        if ($op === Operator::BitAnd) {
            return new AddressValue($this);
        }

        throw OperationError::undefinedOperator($op, $this);
    }

    public function operateOn(Operator $op, GoValue $rhs): self|BoolValue
    {
        assert_values_compatible($this, $rhs);

        return match ($op) {
            Operator::Plus => $this->add($rhs),
            Operator::EqEq => $this->equals($rhs),
            Operator::NotEq => $this->equals($rhs)->invert(),
            default => throw OperationError::undefinedOperator($op, $this),
        };
    }

    public function mutate(Operator $op, GoValue $rhs): void
    {
        $this->onMutate();

        assert_values_compatible($this, $rhs);

        match ($op) {
            Operator::Eq => $this->value = $rhs->value,
            Operator::PlusEq => $this->mutAdd($rhs),
            default => throw OperationError::undefinedOperator($op, $this),
        };
    }

    public function unwrap(): string
    {
        return $this->value;
    }

    public function add(self $value): self
    {
        return new self($this->value . $value->value);
    }

    public function mutAdd(self $value): void
    {
        $this->value .= $value->value;
        $this->byteLen += \strlen($value->value);
    }

    public function copy(): static
    {
        return clone $this;
    }

    public function equals(GoValue $rhs): BoolValue
    {
        return new BoolValue($this->value === $rhs->unwrap());
    }

    public function greater(self $other): BoolValue
    {
        // TODO: Implement greater() method.
    }

    public function greaterEq(self $other): BoolValue
    {
        // TODO: Implement greaterEq() method.
    }

    public function less(self $other): BoolValue
    {
        // TODO: Implement less() method.
    }

    public function lessEq(self $other): BoolValue
    {
        // TODO: Implement lessEq() method.
    }

    public function get(GoValue $at): UntypedIntValue
    {
        assert_index_value($at, BaseIntValue::class, self::NAME);
        assert_index_exists($int = $at->unwrap(), $this->byteLen);

        return UntypedIntValue::fromRune($this->value[$int]);
    }

    public function set(GoValue $value, GoValue $at): void
    {
        throw new \Exception('cannot assign to %s (value of type byte)');
    }

    public function len(): int
    {
        return $this->byteLen;
    }

    /**
     * @return iterable<UntypedIntValue, UntypedIntValue>
     */
    public function iter(): iterable
    {
        $i = 0;
        foreach (\mb_str_split($this->value) as $char) {
            yield new UntypedIntValue($i) => UntypedIntValue::fromRune($char);
            $i += \strlen($char);
        }
    }
}
