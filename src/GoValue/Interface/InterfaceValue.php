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
use GoPhp\Operator;

use function GoPhp\assert_nil_comparison;

use const GoPhp\NIL;

/**
 * @template-implements AddressableValue<never>
 */
final class InterfaceValue implements AddressableValue
{
    use AddressableTrait;

    public const NAME = 'interface';

    public function __construct(
        public readonly ?AddressableValue $value,
        // fixme add any
        public readonly InterfaceType $type = new InterfaceType(),
    ) {}

    public static function nil(InterfaceType $type): self
    {
        return new self(NIL, $type);
    }

    public function copy(): AddressableValue
    {
        return $this;
    }

    public function operate(Operator $op): GoValue
    {
        throw InternalError::unimplemented();
    }

    public function operateOn(Operator $op, GoValue $rhs): GoValue
    {
        assert_nil_comparison($this, $rhs, self::NAME);

        return match ($op) {
            Operator::EqEq => new BoolValue($this->value === NIL),
            Operator::NotEq => new BoolValue($this->value !== NIL),
            default => throw RuntimeError::undefinedOperator($op, $this),
        };
    }

    public function mutate(Operator $op, GoValue $rhs): void
    {
        throw InternalError::unimplemented();
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
        // fixme implement
        return $this->value?->toString() ?? 'nil';
    }
}
