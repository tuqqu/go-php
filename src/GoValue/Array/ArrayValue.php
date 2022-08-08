<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Array;

use GoPhp\Error\OperationError;
use GoPhp\Error\TypeError;
use GoPhp\GoType\ArrayType;
use GoPhp\GoType\SliceType;
use GoPhp\GoValue\AddressValue;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Int\BaseIntValue;
use GoPhp\GoValue\Int\UntypedIntValue;
use GoPhp\GoValue\NamedTrait;
use GoPhp\GoValue\Sequence;
use GoPhp\GoValue\Slice\SliceValue;
use GoPhp\GoValue\Sliceable;
use GoPhp\Operator;

use function GoPhp\assert_index_exists;
use function GoPhp\assert_index_int;
use function GoPhp\assert_slice_indices;
use function GoPhp\assert_types_compatible;
use function GoPhp\assert_values_compatible;

/**
 * @template V of GoValue
 * @template-implements Sequence<BaseIntValue, V>
 */
final class ArrayValue implements Sliceable, Sequence, GoValue
{
    use NamedTrait;

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

        if ($type->len !== $this->len) {
            throw new TypeError(\sprintf('Expected array of length %d, got %d', $type->len, $this->len));
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

        assert_slice_indices($this->len, $low, $high, $max);

        $sliceType = SliceType::fromArrayType($this->type);

        return SliceValue::fromUnderlyingArray($this->values, $sliceType, $low, $high, $cap);
    }

    public function get(GoValue $at): GoValue
    {
        assert_index_int($at, self::NAME);
        assert_index_exists($int = $at->unwrap(), $this->len);

        return $this->values[$int];
    }

    public function set(GoValue $value, GoValue $at): void
    {
        assert_index_int($at, self::NAME);
        assert_index_exists($int = $at->unwrap(), $this->len);
        assert_types_compatible($value->type(), $this->type->elemType);

        $this->values[$int] = $value;
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

    public function operate(Operator $op): AddressValue
    {
        if ($op === Operator::BitAnd) {
            return AddressValue::fromValue($this);
        }

        throw OperationError::undefinedOperator($op, $this);
    }

    public function operateOn(Operator $op, GoValue $rhs): BoolValue
    {
        assert_values_compatible($this, $rhs);

        return match ($op) {
            Operator::EqEq => $this->equals($rhs),
            Operator::NotEq => $this->equals($rhs)->invert(),
            default => throw OperationError::undefinedOperator($op, $this),
        };
    }

    public function equals(GoValue $rhs): BoolValue
    {
        foreach ($this->values as $k => $v) {
            /** @var GoValue $v */
            if (!$v->equals($rhs->values[$k])->unwrap()) {
                return BoolValue::false();
            }
        }

        return BoolValue::true();
    }

    public function mutate(Operator $op, GoValue $rhs): void
    {
        if ($op === Operator::Eq) {
            assert_types_compatible($this->type, $rhs->type());

            $this->values = $rhs->copy()->values;

            return;
        }

        throw OperationError::undefinedOperator($op, $this);
    }

    /**
     * @return V[]
     */
    public function unwrap(): array
    {
        return $this->values->array;
    }

    public function type(): ArrayType
    {
        return $this->type;
    }

    public function copy(): self
    {
        $self = new self(
            $this->values->copyItems(),
            $this->type,
        );

        if ($this->isNamed()) {
            $self->makeNamed();
        }

        return $self;
    }
}
