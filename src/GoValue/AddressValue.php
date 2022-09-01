<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Error\OperationError;
use GoPhp\GoType\PointerType;
use GoPhp\Operator;

use function GoPhp\assert_values_compatible;

final class AddressValue implements AddressableValue
{
    use AddressableTrait;

    private function __construct(
        private GoValue $pointsTo,
        private readonly PointerType $type,
    ) {}

    public static function fromValue(GoValue $value): self
    {
        return new self(
            pointsTo: $value,
            type: new PointerType($value->type()),
        );
    }

    public static function fromType(PointerType $type): self
    {
        return new self(
            pointsTo: new NilValue($type),
            type: $type,
        );
    }

    public function getPointsTo(): GoValue
    {
        return $this->pointsTo;
    }

    public function unwrap(): int
    {
        return $this->getAddress();
    }

    public function operate(Operator $op): GoValue
    {
        return match ($op) {
            Operator::Mul => $this->pointsTo,
            Operator::BitAnd => AddressValue::fromValue($this),
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

    public function mutate(Operator $op, GoValue $rhs): void
    {
        if ($op === Operator::Eq) {
            assert_values_compatible($this, $rhs);

            /** @var self $rhs */
            $this->pointsTo = $rhs->pointsTo;

            return;
        }

        throw OperationError::undefinedOperator($op, $this);
    }

    public function copy(): self
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
