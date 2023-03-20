<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Slice;

use GoPhp\Error\RuntimeError;
use GoPhp\GoType\SliceType;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\Unpackable;
use GoPhp\GoValue\UntypedNilValue;
use GoPhp\GoValue\PointerValue;
use GoPhp\GoValue\Array\UnderlyingArray;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Int\IntNumber;
use GoPhp\GoValue\Int\UntypedIntValue;
use GoPhp\GoValue\AddressableTrait;
use GoPhp\GoValue\Sequence;
use GoPhp\GoValue\Sliceable;
use GoPhp\Operator;

use function GoPhp\assert_index_exists;
use function GoPhp\assert_index_int;
use function GoPhp\assert_nil_comparison;
use function GoPhp\assert_index_sliceable;
use function GoPhp\assert_values_compatible;

use function implode;

use function sprintf;

use const GoPhp\NIL;

/**
 * @template V of AddressableValue
 *
 * @template-implements Sequence<IntNumber, V>
 * @template-implements Unpackable<V>
 * @template-implements AddressableValue<array<V>>
 */
final class SliceValue implements Sliceable, Unpackable, Sequence, AddressableValue
{
    use AddressableTrait;

    public const NAME = 'slice';

    public readonly SliceType $type;
    private int $pos = 0;

    private int $len;
    private int $cap;

    /** @var UnderlyingArray<V>|null */
    private ?UnderlyingArray $values;

    /**
     * @param UnderlyingArray<V>|null $array
     */
    private function __construct(
        ?UnderlyingArray $array,
        SliceType $type,
        ?int $cap = null,
    ) {
        $this->values = $array;
        $this->len = $this->values?->count() ?? 0;
        $this->cap = $cap ?? $this->len;
        $this->type = $type;
    }

    /**
     * @template T of AddressableValue
     *
     * @param T[] $values
     * @return self<T>
     */
    public static function fromValues(array $values, SliceType $type, ?int $cap = null): self
    {
        return new self(new UnderlyingArray($values), $type, $cap);
    }

    public static function nil(SliceType $type): self
    {
        return new self(NIL, $type);
    }

    /**
     * @template T of AddressableValue
     *
     * @param UnderlyingArray<T>|null $array
     * @return self<T>
     */
    public static function fromUnderlyingArray(
        ?UnderlyingArray $array,
        SliceType $type,
        int $pos,
        int $len,
        int $cap,
    ): self {
        $slice = new self($array, $type, $cap);
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

        return sprintf('[%s]', implode(' ', $str));
    }

    public function slice(?int $low, ?int $high, ?int $max = null): self
    {
        $low ??= 0;
        $high ??= $this->len;
        $cap = $max === null ? $this->len - $low : $max - $low;

        assert_index_sliceable($this->len, $low, $high, $max);

        return self::fromUnderlyingArray($this->values, $this->type, $low, $high, $cap);
    }

    public function get(GoValue $at): GoValue
    {
        assert_index_int($at, self::NAME);
        assert_index_exists($int = (int) $at->unwrap(), $this->len);

        return $this->accessUnderlyingArray()[$int];
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

    public function unpack(): iterable
    {
        yield from $this->accessUnderlyingArray();
    }

    /**
     * Assumes that types are compatible.
     *
     * @param V $value
     */
    public function append(GoValue $value): void
    {
        if ($this->values === NIL) {
            /** @var UnderlyingArray<V> $this->values */
            $this->values = UnderlyingArray::fromEmpty();
        }

        if ($this->exceedsCapacity()) {
            $this->grow();
        }

        /** @psalm-suppress PossiblyNullReference */
        $this->values[$this->len++] = $value;
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
        assert_nil_comparison($this, $rhs, self::NAME);

        return match ($op) {
            Operator::EqEq => new BoolValue($this->values === NIL),
            Operator::NotEq => new BoolValue($this->values !== NIL),
            default => throw RuntimeError::undefinedOperator($op, $this),
        };
    }

    public function mutate(Operator $op, GoValue $rhs): void
    {
        if ($op === Operator::Eq) {
            if ($rhs instanceof UntypedNilValue) {
                $this->values = NIL;
                $this->len = 0;
                $this->cap = 0;
                $this->pos = 0;

                return;
            }

            assert_values_compatible($this, $rhs);

            $this->morph($rhs);

            return;
        }

        throw RuntimeError::undefinedOperator($op, $this);
    }

    public function unwrap(): array
    {
        return $this->values?->values() ?? [];
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

    /**
     * Types must be checked beforehand
     *
     * @param V $value
     */
    public function setBlindly(GoValue $value, int $at): void
    {
        /** @psalm-suppress PossiblyNullReference */
        $this->values[$at + $this->pos] = $value;
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

        $this->pos = 0;
        if ($this->cap === 0) {
            $this->cap = 1;
        } else {
            $this->cap <<= 1;
        }

        /** @var V[] $copies */
        $this->values = new UnderlyingArray($copies);
    }

    /**
     * @return V[]
     */
    private function accessUnderlyingArray(): array
    {
        return $this->values?->slice($this->pos, $this->len - $this->pos) ?? [];
    }

    /**
     * @param self<V> $other
     */
    private function morph(self $other): void
    {
        $this->values = $other->values;
        $this->pos = $other->pos;
        $this->len = $other->len;
        $this->cap = $other->cap;
    }
}
