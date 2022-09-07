<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\GoType\GoType;
use GoPhp\Operator;
use GoPhp\GoType\NamedType;
use GoPhp\Error\OperationError;

use function GoPhp\assert_values_compatible;

final class BoolValue implements NonRefValue, Sealable, AddressableValue
{
    use SealableTrait;
    use AddressableTrait;

    public function __construct(
        private bool $value,
    ) {}

    public static function true(): self
    {
        return new self(true);
    }

    public static function false(): self
    {
        return new self(false);
    }

    public function isTrue(): bool
    {
        return $this->value;
    }

    public function isFalse(): bool
    {
        return !$this->value;
    }

    public function reify(?GoType $with = null): NonRefValue
    {
        return $this;
    }

    public function toString(): string
    {
        return $this->value ? 'true' : 'false';
    }

    public static function create(mixed $value): self
    {
        return new self($value);
    }

    public function type(): NamedType
    {
        return NamedType::Bool;
    }

    public function unwrap(): bool
    {
        return $this->value;
    }

    public function invert(): self
    {
        return new self(!$this->value);
    }

    public function operate(Operator $op): self|PointerValue
    {
        return match ($op) {
            Operator::BitAnd => PointerValue::fromValue($this),
            Operator::LogicNot => $this->invert(),
            default => throw OperationError::undefinedOperator($op, $this, true),
        };
    }

    public function operateOn(Operator $op, GoValue $rhs): self
    {
        assert_values_compatible($this, $rhs);

        return match ($op) {
            Operator::LogicAnd => $this->logicAnd($rhs),
            Operator::LogicOr => $this->logicOr($rhs),
            Operator::EqEq => $this->equals($rhs),
            Operator::NotEq => $this->equals($rhs)->invert(),
            default => throw OperationError::undefinedOperator($op, $this),
        };
    }

    public function mutate(Operator $op, GoValue $rhs): void
    {
        $this->onMutate();

        if ($op === Operator::Eq) {
            assert_values_compatible($this, $rhs);

            $this->value = $rhs->value;

            return;
        }

        throw OperationError::undefinedOperator($op, $this);
    }

    public function copy(): self
    {
        $cloned = clone $this;
        $cloned->sealed = false;

        return $cloned;
    }

    private function equals(self $rhs): self
    {
        return new self($this->value === $rhs->value);
    }

    private function logicOr(self $other): self
    {
        return new self($this->value || $other->value);
    }

    private function logicAnd(self $other): self
    {
        return new self($this->value && $other->value);
    }
}
