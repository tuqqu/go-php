<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Error\RuntimeError;
use GoPhp\Error\PanicError;
use GoPhp\GoType\PointerType;
use GoPhp\Operator;

use function spl_object_id;
use function sprintf;
use function GoPhp\assert_values_compatible;

/**
 * @psalm-type Address = int
 * @template-implements AddressableValue<Address>
 */
final class PointerValue implements AddressableValue
{
    use AddressableTrait;

    private function __construct(
        private ?AddressableValue $pointsTo,
        private readonly PointerType $type,
    ) {}

    public static function fromValue(AddressableValue $value): self
    {
        return new self($value, new PointerType($value->type()));
    }

    public static function nil(PointerType $type): self
    {
        return new self(NIL, $type);
    }

    public function getPointsTo(): AddressableValue
    {
        if ($this->pointsTo === NIL) {
            throw PanicError::nilDereference();
        }

        return $this->pointsTo;
    }

    public function unwrap(): int
    {
        return $this->getAddress();
    }

    public function operate(Operator $op): AddressableValue
    {
        return match ($op) {
            Operator::Mul => $this->getPointsTo(),
            Operator::BitAnd => PointerValue::fromValue($this),
            default => throw RuntimeError::undefinedOperator($op, $this),
        };
    }

    public function operateOn(Operator $op, GoValue $rhs): BoolValue
    {
        assert_values_compatible($this, $rhs);

        return match ($op) {
            Operator::EqEq => $this->equals($rhs),
            Operator::NotEq => $this->equals($rhs)->invert(),
            default => throw RuntimeError::undefinedOperator($op, $this),
        };
    }

    public function mutate(Operator $op, GoValue $rhs): void
    {
        if ($op === Operator::Eq) {
            assert_values_compatible($this, $rhs);

            if ($rhs instanceof UntypedNilValue) {
                $this->pointsTo = NIL;

                return;
            }

            $this->pointsTo = $rhs->pointsTo;

            return;
        }

        throw RuntimeError::undefinedOperator($op, $this);
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
        return sprintf('0x%x', $this->getAddress());
    }

    private function getAddress(): int
    {
        if ($this->pointsTo === NIL) {
            return ZERO_ADDRESS;
        }

        return spl_object_id($this->pointsTo);
    }

    private function equals(self|UntypedNilValue $rhs): BoolValue
    {
        if ($rhs instanceof UntypedNilValue) {
            return new BoolValue($this->pointsTo === NIL);
        }

        return new BoolValue($rhs->pointsTo === $this->pointsTo);
    }
}
