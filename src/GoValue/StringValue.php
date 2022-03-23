<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Operator;
use GoPhp\GoType\BasicType;
use GoPhp\Error\UnknownOperationError;

final class StringValue implements Addable, Comparable
{
    public function __construct(
        private readonly string $value,
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
        return match ($op) {
            Operator::Plus,
            Operator::PlusEq => $this->add($rhs),
            Operator::EqEq => $this->equals($rhs),
            Operator::NotEq => $this->equals($rhs)->invert(),
            default => throw UnknownOperationError::unknownOperator($op),
        };
    }

    public function unwrap(): string
    {
        return $this->value;
    }

    public function add(Addable $value): static
    {
        // fixme add type check
        return new self($this->value . $value->value);
    }

    public function equals(GoValue $rhs): BoolValue
    {
        // fixme add type check
        return BoolValue::fromBool($this->value === $rhs->unwrap());
    }

    public function greater(Comparable $other): BoolValue
    {
        // TODO: Implement greater() method.
    }

    public function greaterEq(Comparable $other): BoolValue
    {
        // TODO: Implement greaterEq() method.
    }

    public function less(Comparable $other): BoolValue
    {
        // TODO: Implement less() method.
    }

    public function lessEq(Comparable $other): BoolValue
    {
        // TODO: Implement lessEq() method.
    }
}
