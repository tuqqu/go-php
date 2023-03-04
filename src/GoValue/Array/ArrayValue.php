<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Array;

use GoPhp\Error\RuntimeError;
use GoPhp\GoType\ArrayType;
use GoPhp\GoType\SliceType;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\Hashable;
use GoPhp\GoValue\PointerValue;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Int\IntNumber;
use GoPhp\GoValue\Int\UntypedIntValue;
use GoPhp\GoValue\AddressableTrait;
use GoPhp\GoValue\Sequence;
use GoPhp\GoValue\Slice\SliceValue;
use GoPhp\GoValue\Sliceable;
use GoPhp\Operator;

use function GoPhp\assert_index_exists;
use function GoPhp\assert_index_int;
use function GoPhp\assert_map_key;
use function GoPhp\assert_index_sliceable;
use function GoPhp\assert_values_compatible;

/**
 * @template V of AddressableValue
 *
 * @template-implements Sequence<IntNumber, V>
 * @template-implements AddressableValue<array<V>>
 * @template-implements Hashable<string>
 */
final class ArrayValue implements Hashable, Sliceable, Sequence, AddressableValue
{
    use AddressableTrait;

    public const NAME = 'array';

    /** @var UnderlyingArray<V> */
    private UnderlyingArray $values;
    private readonly ArrayType $type;
    private readonly int $len;

    /**
     * @param V[] $values
     */
    public function __construct(array $values, ArrayType $type)
    {
        $this->values = new UnderlyingArray($values);
        $this->len = $this->values->count();
        $type->finish($this->len);

        if ($type->getLen() !== $this->len) {
            throw RuntimeError::indexOutOfBounds($this->len - 1, $type->getLen());
        }

        $this->type = $type;
    }

    public function toString(): string
    {
        $str = [];
        foreach ($this->values as $value) {
            $str[] = $value->toString();
        }

        return \sprintf('[%s]', \implode(' ', $str));
    }

    public function slice(?int $low, ?int $high, ?int $max = null): SliceValue
    {
        $low ??= 0;
        $high ??= $this->len;
        $cap = $max === null ? $this->len - $low : $max - $low;

        assert_index_sliceable($this->len, $low, $high, $max);

        $sliceType = SliceType::fromArrayType($this->type);

        return SliceValue::fromUnderlyingArray($this->values, $sliceType, $low, $high, $cap);
    }

    public function get(GoValue $at): GoValue
    {
        assert_index_int($at, self::NAME);
        assert_index_exists($int = (int) $at->unwrap(), $this->len);

        return $this->values[$int];
    }

    public function len(): int
    {
        return $this->len;
    }

    public function iter(): iterable
    {
        foreach ($this->values as $key => $value) {
            yield new UntypedIntValue($key) => $value;
        }
    }

    public function operate(Operator $op): PointerValue
    {
        if ($op === Operator::BitAnd) {
            return PointerValue::fromValue($this);
        }

        throw RuntimeError::undefinedOperator($op, $this, true);
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
            $this->values = $rhs->copy()->values;

            return;
        }

        throw RuntimeError::undefinedOperator($op, $this);
    }

    /**
     * @return array<V>
     */
    public function unwrap(): array
    {
        return $this->values->values();
    }

    public function type(): ArrayType
    {
        return $this->type;
    }

    /**
     * @return static<V>
     */
    public function copy(): static
    {
        $self = new self(
            $this->values->copyItems(),
            $this->type,
        );

        if ($this->isAddressable()) {
            $self->makeAddressable();
        }

        return $self;
    }

    /**
     * @param self<V> $rhs
     */
    private function equals(self $rhs): BoolValue
    {
        foreach ($this->values as $k => $v) {
            if (!$v->operateOn(Operator::EqEq, $rhs->values[$k])->unwrap()) {
                return BoolValue::false();
            }
        }

        return BoolValue::true();
    }

    public function hash(): string
    {
        $hash = self::NAME;

        foreach ($this->values as $value) {
            assert_map_key($value);

            $hash .= \sprintf(':%s', $value->hash());
        }

        return $hash;
    }
}
