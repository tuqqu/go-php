<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Operator;
use GoPhp\GoType\BasicType;
use GoPhp\Error\UnknownOperationError;
use function GoPhp\assert_type_conforms;

final class StringValue implements GoValue
{
    public function __construct(
        private string $value,
    ) {}

    public function type(): BasicType
    {
        return BasicType::String;
    }

    public function operate(Operator $op): never
    {
        throw UnknownOperationError::unknownOperator($op);
    }

    public function operateOn(Operator $op, GoValue $rhs): self|BoolValue
    {
        assert_type_conforms($this, $rhs);

        return match ($op) {
            Operator::Plus,
            Operator::EqEq => $this->equals($rhs),
            Operator::NotEq => $this->equals($rhs)->invert(),
            default => throw UnknownOperationError::unknownOperator($op),
        };
    }

    public function mutate(Operator $op, GoValue $rhs): void
    {
        assert_type_conforms($this, $rhs);

        if ($op === Operator::PlusEq) {
            $this->mutAdd($rhs);
        }

        throw UnknownOperationError::unknownOperator($op);
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
}
