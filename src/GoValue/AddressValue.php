<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

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
        if ($op === Operator::Mul) {
            return $this->pointsTo;
        }

        if ($op === Operator::BitAnd) {
            return new AddressValue($this);
        }

        throw new \Exception();
    }

    public function operateOn(Operator $op, GoValue $rhs): BoolValue
    {
        throw new \Exception();
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
        throw new \Exception();
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

    public function pointTo(GoValue $value): void
    {
        $this->pointsTo = $value;
    }

    private function getAddress(): int
    {
        return \spl_object_id($this->pointsTo);
    }
}
