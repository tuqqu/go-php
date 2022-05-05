<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Error\OperationError;
use GoPhp\GoType\PointerType;
use GoPhp\Operator;

final class AddressValue implements GoValue
{
    public readonly PointerType $type;

    public function __construct(
        public GoValue $pointsTo,
    ) {
        $this->type = new PointerType($this->pointsTo->type());
    }

    public function unwrap(): int
    {
        return $this->getAddress();
    }

    public function operate(Operator $op): GoValue
    {
        return match ($op) {
            Operator::Mul => $this->pointsTo,
            Operator::BitAnd => new AddressValue($this),
            default => throw OperationError::undefinedOperator($op, $this),
        };
    }

    public function operateOn(Operator $op, GoValue $rhs): BoolValue
    {
        throw OperationError::undefinedOperator($op, $this);
    }

    public function equals(GoValue $rhs): BoolValue
    {
        return new BoolValue(
            $rhs instanceof self
            && $rhs->pointsTo === $this->pointsTo
        );
    }

    public function mutate(Operator $op, GoValue $rhs): never
    {
        throw OperationError::undefinedOperator($op, $this);
    }

    public function copy(): static
    {
        return $this;
    }

    public function type(): PointerType
    {
       return $this->type;
    }

    public function toString(): string
    {
        return \sprintf('0x%x', $this->getAddress());
    }

    private function getAddress(): int
    {
        return \spl_object_id($this->pointsTo);
    }
}
