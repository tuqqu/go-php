<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Error\OperationError;
use GoPhp\GoType\BasicType;
use GoPhp\GoValue\Int\BaseIntValue;
use GoPhp\GoValue\Int\Int32Value;
use GoPhp\Operator;
use function GoPhp\assert_index_exists;
use function GoPhp\assert_index_value;
use function GoPhp\assert_values_compatible;

final class StringValue implements Sequence, GoValue
{
    public const NAME = 'string';

    private int $len;

    public function __construct(
        private string $value,
    ) {
        $this->len = \strlen($this->value);
    }

    public function type(): BasicType
    {
        return BasicType::String;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function operate(Operator $op): never
    {
        throw OperationError::unknownOperator($op, $this);
    }

    public function operateOn(Operator $op, GoValue $rhs): self|BoolValue
    {
        assert_values_compatible($this, $rhs);

        return match ($op) {
            Operator::Plus,
            Operator::EqEq => $this->equals($rhs),
            Operator::NotEq => $this->equals($rhs)->invert(),
            default => throw OperationError::unknownOperator($op, $this),
        };
    }

    public function mutate(Operator $op, GoValue $rhs): void
    {
        assert_values_compatible($this, $rhs);

        if ($op === Operator::PlusEq) {
            $this->mutAdd($rhs);
        }

        throw OperationError::unknownOperator($op, $this);
    }

    public function unwrap(): string
    {
        return $this->value;
    }

    public function add(self $value): static
    {
        return new self($this->value . $value->value);
    }

    public function mutAdd(self $value): void
    {
        $this->value .= $value->value;
        $this->len += \strlen($value->value);
    }

    public function copy(): static
    {
        return clone $this;
    }

    public function equals(GoValue $rhs): BoolValue
    {
        return BoolValue::fromBool($this->value === $rhs->unwrap());
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

    public function get(GoValue $at): Int32Value
    {
        assert_index_value($at, BaseIntValue::class, self::NAME);
        assert_index_exists($int = $at->unwrap(), $this->len);

        return Int32Value::fromRune($this->value[$int]);
    }

    public function set(GoValue $value, GoValue $at): void
    {
        throw new \Exception('cannot assign to %s (value of type byte)');
    }

    public function len(): int
    {
        return $this->len;
    }
}
