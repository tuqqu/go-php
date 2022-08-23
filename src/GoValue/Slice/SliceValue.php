<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Slice;

use GoPhp\Error\OperationError;
use GoPhp\GoType\SliceType;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\AddressValue;
use GoPhp\GoValue\Array\UnderlyingArray;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Int\BaseIntValue;
use GoPhp\GoValue\Int\UntypedIntValue;
use GoPhp\GoValue\AddressableTrait;
use GoPhp\GoValue\Sequence;
use GoPhp\GoValue\Sliceable;
use GoPhp\Operator;

use function GoPhp\assert_index_exists;
use function GoPhp\assert_index_int;
use function GoPhp\assert_nil_comparison;
use function GoPhp\assert_slice_indices;
use function GoPhp\assert_types_compatible;

/**
 * @template V of GoValue
 * @template-implements Sequence<BaseIntValue, V>
 */
final class SliceValue implements Sliceable, Sequence, AddressableValue
{
    use AddressableTrait;

    public const NAME = 'slice';

    public readonly SliceType $type;
    private bool $nil = false;
    private int $pos = 0;

    private int $len;
    private int $cap;

    /** @var UnderlyingArray<V> */
    private UnderlyingArray $values;

    /**
     * @param V[] $values
     */
    public function __construct(
        array $values,
        SliceType $type,
        ?int $cap = null,
    ) {
        $this->values = new UnderlyingArray($values);
        $this->len = $this->values->count();
        $this->cap = $cap ?? $this->len;
        $this->type = $type;
    }

    public static function nil(SliceType $type): self
    {
        $slice = new self([], $type);
        $slice->nil = true;

        return $slice;
    }

    /**
     * @template T of GoValue
     * @param UnderlyingArray<T> $array
     * @return self<T>
     */
    public static function fromUnderlyingArray(
        UnderlyingArray $array,
        SliceType $type,
        int $pos,
        int $len,
        int $cap,
    ): self {
        // fixme constructor
        /** @var self<T> $slice */
        $slice = new self([], $type, $cap);
        $slice->values = $array;
        $slice->len = $len;
        $slice->pos = $pos;

        return $slice;
    }

    public function toString(): string
    {
        $str = [];
        foreach ($this->accessUnderlyingArray() as $value) {
            $str[] = $value->toString();
        }

        return \sprintf('[%s]', \implode(' ', $str));
    }

    public function slice(?int $low, ?int $high, ?int $max = null): self
    {
        $low ??= 0;
        $high ??= $this->len;
        $cap = $max === null ? $this->len - $low : $max - $low;

        assert_slice_indices($this->len, $low, $high, $max);

        return self::fromUnderlyingArray($this->values, $this->type, $low, $high, $cap);
    }

    public function get(GoValue $at): GoValue
    {
        assert_index_int($at, self::NAME);
        assert_index_exists($int = $at->unwrap(), $this->len);

        return $this->accessUnderlyingArray()[$int];
    }

    public function set(GoValue $value, GoValue $at): void
    {
        assert_index_int($at, self::NAME);
        assert_index_exists($int = $at->unwrap(), $this->len);
        assert_types_compatible($value->type(), $this->type->elemType);

        $this->values[$int + $this->pos] = $value;
    }

    public function len(): int
    {
        return $this->len - $this->pos;
    }

    public function cap(): int
    {
        return $this->cap;
    }

    public function iter(): iterable
    {
        foreach ($this->accessUnderlyingArray() as $key => $value) {
            yield new UntypedIntValue($key) => $value;
        }
    }

    /**
     * @param V $value
     */
    public function append(GoValue $value): void
    {
        //fixme change error text
        assert_types_compatible($value->type(), $this->type->elemType);

        if ($this->exceedsCapacity()) {
            $this->grow();
        }

        $this->values[$this->len++] = $value;
    }

    public function operate(Operator $op): AddressValue
    {
        if ($op === Operator::BitAnd) {
            return AddressValue::fromValue($this);
        }

        throw OperationError::undefinedOperator($op, $this, true);
    }

    public function operateOn(Operator $op, GoValue $rhs): BoolValue
    {
        assert_nil_comparison($this, $rhs);

        return match ($op) {
            Operator::EqEq => BoolValue::false(),
            Operator::NotEq => BoolValue::true(),
            default => throw OperationError::undefinedOperator($op, $this),
        };
    }

    public function equals(GoValue $rhs): BoolValue
    {
        return BoolValue::false();
    }

    public function mutate(Operator $op, GoValue $rhs): void
    {
        if ($op === Operator::Eq) {
            assert_types_compatible($this->type, $rhs->type());
            /** @var self<V> $rhs */
            $this->morph($rhs);

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

    public function type(): SliceType
    {
        return $this->type;
    }

    public function copy(): self
    {
        return $this;
    }

    public function clone(): self
    {
        return self::fromUnderlyingArray(
            $this->values,
            $this->type,
            $this->pos,
            $this->len,
            $this->cap,
        );
    }

    private function exceedsCapacity(): bool
    {
        return $this->len - $this->pos + 1 > $this->cap;
    }

    private function grow(): void
    {
        $copies = [];
        foreach ($this->accessUnderlyingArray() as $value) {
            $copies[] = $value->copy();
        }

        $this->cap *= 2;
        $this->pos = 0;

        /** @var V[] $copies */
        $this->values = new UnderlyingArray($copies);
    }

    /**
     * @return V[]
     */
    private function accessUnderlyingArray(): array
    {
        return $this->values->slice($this->pos, $this->len - $this->pos);
    }

    /**
     * @param self<V> $other
     */
    private function morph(self $other): void
    {
        $this->values = $other->values;
        $this->nil = $other->nil;
        $this->pos = $other->pos;
        $this->len = $other->len;
        $this->cap = $other->cap;
    }
}
