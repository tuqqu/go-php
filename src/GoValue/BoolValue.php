<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Error\RuntimeError;
use GoPhp\GoType\GoType;
use GoPhp\Operator;
use GoPhp\GoType\NamedType;

use function GoPhp\assert_values_compatible;

/**
 * @template-implements Hashable<bool>
 * @template-implements AddressableValue<bool>
 */
final class BoolValue implements Hashable, Castable, Sealable, AddressableValue
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

    public function toString(): string
    {
        return $this->value ? 'true' : 'false';
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
            Operator::LogicNot => $this->invert(),
            Operator::BitAnd => $this->isAddressable()
                ? PointerValue::fromValue($this)
                : throw RuntimeError::cannotTakeAddressOfValue($this),
            default => throw RuntimeError::undefinedOperator($op, $this, true),
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
            default => throw RuntimeError::undefinedOperator($op, $this),
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

        throw RuntimeError::undefinedOperator($op, $this);
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

    public function hash(): bool
    {
        return $this->unwrap();
    }

    public function cast(GoType $to): self
    {
        return $this;
    }
}
