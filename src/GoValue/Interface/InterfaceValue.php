<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Interface;

use GoPhp\Error\InternalError;
use GoPhp\Error\RuntimeError;
use GoPhp\GoType\InterfaceType;
use GoPhp\GoValue\AddressableTrait;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Ref;
use GoPhp\GoValue\UntypedNilValue;
use GoPhp\Operator;

use function GoPhp\assert_values_compatible;
use function GoPhp\GoValue\get_address;

use const GoPhp\GoValue\NIL;

/**
 * @template-implements AddressableValue<never>
 */
final class InterfaceValue implements Ref, AddressableValue
{
    use AddressableTrait;

    public const string NAME = 'interface';

    public readonly InterfaceType $type;

    public function __construct(
        public ?AddressableValue $value,
        ?InterfaceType $type = null,
    ) {
        $this->type = $type ?? InterfaceType::any();
    }

    public static function nil(InterfaceType $type): self
    {
        return new self(NIL, $type);
    }

    /**
     * @psalm-assert !null $this->value
     */
    public function isNil(): bool
    {
        return $this->value === NIL;
    }

    public function copy(): AddressableValue
    {
        return $this;
    }

    public function operate(Operator $op): GoValue
    {
        throw InternalError::unimplemented();
    }

    /**
     * @psalm-suppress all
     */
    public function operateOn(Operator $op, GoValue $rhs): GoValue
    {
        if ($rhs instanceof UntypedNilValue) {
            return match ($op) {
                Operator::EqEq => new BoolValue($this->isNil()),
                Operator::NotEq => new BoolValue(!$this->isNil()),
                default => throw RuntimeError::undefinedOperator($op, $this),
            };
        }

        if ($rhs instanceof self) {
            assert_values_compatible($this, $rhs);

            if ($this->isNil() || $rhs->isNil()) {
                return match ($op) {
                    Operator::EqEq => new BoolValue($this->isNil() && $rhs->isNil()),
                    Operator::NotEq => new BoolValue(!$this->isNil() || !$rhs->isNil()),
                    default => throw RuntimeError::undefinedOperator($op, $this),
                };
            }

            return match ($op) {
                Operator::EqEq => $this->value->operateOn(Operator::EqEq, $rhs->value),
                Operator::NotEq => $this->value->operateOn(Operator::NotEq, $rhs->value),
                default => throw RuntimeError::undefinedOperator($op, $this),
            };
        }

        assert_values_compatible($this->value, $rhs);

        return match ($op) {
            Operator::EqEq => $this->value->operateOn(Operator::EqEq, $rhs),
            Operator::NotEq => $this->value->operateOn(Operator::NotEq, $rhs),
            default => throw RuntimeError::undefinedOperator($op, $this),
        };
    }

    public function mutate(Operator $op, GoValue $rhs): void
    {
        if ($op === Operator::Eq) {
            if ($rhs instanceof UntypedNilValue) {
                $this->value = NIL;

                return;
            }

            assert_values_compatible($this, $rhs);

            $this->value = $rhs;

            return;
        }

        throw RuntimeError::undefinedOperator($op, $this);
    }

    public function unwrap(): mixed
    {
        throw InternalError::unimplemented();
    }

    public function type(): InterfaceType
    {
        return $this->type;
    }

    public function toString(): string
    {
        return get_address($this);
    }
}
