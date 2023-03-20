<?php

declare(strict_types=1);

namespace GoPhp\GoValue\String;

use GoPhp\Error\RuntimeError;
use GoPhp\GoType\NamedType;
use GoPhp\GoType\UntypedType;
use GoPhp\GoValue\AddressableTrait;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\Castable;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Hashable;
use GoPhp\GoValue\Int\IntNumber;
use GoPhp\GoValue\Int\Uint8Value;
use GoPhp\GoValue\Int\UntypedIntValue;
use GoPhp\GoValue\Typeable;
use GoPhp\GoValue\PointerValue;
use GoPhp\GoValue\Sealable;
use GoPhp\GoValue\SealableTrait;
use GoPhp\GoValue\Sequence;
use GoPhp\GoValue\Sliceable;
use GoPhp\GoValue\TypeableTrait;
use GoPhp\GoValue\Unpackable;
use GoPhp\Operator;

use function mb_str_split;
use function strlen;
use function substr;
use function GoPhp\assert_index_exists;
use function GoPhp\assert_index_int;
use function GoPhp\assert_index_sliceable;
use function GoPhp\assert_values_compatible;

/**
 * @template-implements Sequence<IntNumber, UntypedIntValue|Uint8Value>
 * @template-implements Unpackable<UntypedIntValue>
 * @template-implements Hashable<string>
 * @template-implements AddressableValue<string>
 */
abstract class BaseString implements
    Sliceable,
    Unpackable,
    Sequence,
    Sealable,
    Hashable,
    Castable,
    Typeable,
    AddressableValue
{
    use TypeableTrait;
    use SealableTrait;
    use AddressableTrait;

    public const NAME = 'string';

    private string $value;
    private int $byteLen;

    public function __construct(string $value)
    {
        $this->value = $value;
        $this->byteLen = strlen($this->value);
    }

    abstract public function type(): NamedType|UntypedType;

    public function toString(): string
    {
        return $this->value;
    }

    public function slice(?int $low, ?int $high, ?int $max = null): static
    {
        if ($max !== null) {
            throw RuntimeError::cannotFullSliceString();
        }

        $low ??= 0;
        $high ??= $this->byteLen;

        assert_index_sliceable($this->byteLen, $low, $high);

        return new static(substr($this->value, $low, $high - $low));
    }

    public function operate(Operator $op): PointerValue
    {
        if ($op === Operator::BitAnd) {
            if (!$this->isAddressable()) {
                throw RuntimeError::cannotTakeAddressOfValue($this);
            }

            return PointerValue::fromValue($this);
        }

        throw RuntimeError::undefinedOperator($op, $this, true);
    }

    public function operateOn(Operator $op, GoValue $rhs): self|BoolValue
    {
        assert_values_compatible($this, $rhs);

        return match ($op) {
            Operator::Plus => $this->add($rhs),
            Operator::EqEq => $this->equals($rhs),
            Operator::NotEq => $this->equals($rhs)->invert(),
            default => throw RuntimeError::undefinedOperator($op, $this, true),
        };
    }

    public function mutate(Operator $op, GoValue $rhs): void
    {
        $this->onMutate();

        assert_values_compatible($this, $rhs);

        match ($op) {
            Operator::Eq => $this->value = $rhs->value,
            Operator::PlusEq => $this->mutAdd($rhs),
            default => throw RuntimeError::undefinedOperator($op, $this),
        };
    }

    public function unwrap(): string
    {
        return $this->value;
    }

    public function add(self $value): static
    {
        return new static($this->value . $value->value);
    }

    public function mutAdd(self $value): void
    {
        $this->value .= $value->value;
        $this->byteLen += strlen($value->value);
    }

    public function copy(): self
    {
        return clone $this;
    }

//    public function greater(self $other): BoolValue
//    {
//        // TODO: Implement greater() method.
//    }
//
//    public function greaterEq(self $other): BoolValue
//    {
//        // TODO: Implement greaterEq() method.
//    }
//
//    public function less(self $other): BoolValue
//    {
//        // TODO: Implement less() method.
//    }
//
//    public function lessEq(self $other): BoolValue
//    {
//        // TODO: Implement lessEq() method.
//    }

    public function get(GoValue $at): Uint8Value
    {
        assert_index_int($at, self::NAME);
        assert_index_exists($int = (int) $at->unwrap(), $this->byteLen);

        $byte = Uint8Value::fromByte($this->value[$int]);
        $byte->seal();

        return $byte;
    }

    public function len(): int
    {
        return $this->byteLen;
    }

    public function iter(): iterable
    {
        $i = 0;
        foreach ($this->chars() as $char) {
            yield new UntypedIntValue($i) => UntypedIntValue::fromRune($char);
            $i += strlen($char);
        }
    }

    public function unpack(): iterable
    {
        foreach ($this->chars() as $char) {
            yield UntypedIntValue::fromRune($char);
        }
    }

    public function hash(): string
    {
        return $this->unwrap();
    }

    public function cast(NamedType $to): self
    {
        return $this;
    }

    final protected function doBecomeTyped(NamedType $type): self
    {
        if ($type !== NamedType::String || !$this->type() instanceof UntypedType) {
            throw RuntimeError::implicitConversionError($this, $type);
        }

        return new StringValue($this->value);
    }

    /**
     * @return iterable<string>
     */
    private function chars(): iterable
    {
        yield from mb_str_split($this->value);
    }

    private function equals(self $rhs): BoolValue
    {
        return new BoolValue($this->value === $rhs->value);
    }
}
