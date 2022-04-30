<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Slice;

use GoPhp\Error\OperationError;
use GoPhp\GoType\SliceType;
use GoPhp\GoValue\Array\UnderlyingArray;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Int\BaseIntValue;
use GoPhp\GoValue\Int\UntypedIntValue;
use GoPhp\GoValue\Sequence;
use GoPhp\Operator;
use function GoPhp\assert_index_exists;
use function GoPhp\assert_index_value;
use function GoPhp\assert_nil_comparison;
use function GoPhp\assert_types_compatible;

final class SliceValue implements Sequence, GoValue
{
    public const NAME = 'slice';

    private UnderlyingArray $values;
    private int $pos = 0;

    public readonly SliceType $type;
    private int $len;
    private int $cap;

    /**
     * @param GoValue[] $values
     */
    public function __construct(
        array $values,
        SliceType $type,
        ?int $cap = null,
    ) {
        $this->values = new UnderlyingArray($values);
        $this->len = \count($values);
        $this->cap = $cap ?? $this->len;
        $this->type = $type;
    }

    public static function fromUnderlyingArray(
        UnderlyingArray $array,
        SliceType $type,
        int $pos,
        int $len,
        int $cap,
    ): self {
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

    public function get(GoValue $at): GoValue
    {
        assert_index_value($at, BaseIntValue::class, self::NAME);
        assert_index_exists($int = $at->unwrap(), $this->len);

        return $this->accessUnderlyingArray()[$int];
    }

    public function set(GoValue $value, GoValue $at): void
    {
        assert_index_value($at, BaseIntValue::class, self::NAME);
        assert_index_exists($int = $at->unwrap(), $this->len);
        assert_types_compatible($value->type(), $this->type->internalType);

        $this->values->array[$int + $this->pos] = $value;
    }

    public function len(): int
    {
        return $this->len - $this->pos;
    }

    public function cap(): int
    {
        return $this->cap;
    }

    /**
     * @return iterable<UntypedIntValue, GoValue>
     */
    public function iter(): iterable
    {
        foreach ($this->accessUnderlyingArray() as $key => $value) {
            yield new UntypedIntValue($key) => $value;
        }
    }

    public function append(GoValue $value): void
    {
        //fixme change error text
        assert_types_compatible($value->type(), $this->type->internalType);

        if ($this->exceedsCapacity()) {
            $this->grow();
        }

        $this->values->array[$this->len++] = $value;
    }

    public function operate(Operator $op): self
    {
        throw new \BadMethodCallException(); //fixme
    }

    public function operateOn(Operator $op, GoValue $rhs): BoolValue
    {
        assert_nil_comparison($this, $rhs);

        return match ($op) {
            Operator::EqEq => BoolValue::False,
            Operator::NotEq => BoolValue::True,
            default => throw OperationError::unknownOperator($op, $this),
        };
    }

    public function equals(GoValue $rhs): BoolValue
    {
        return BoolValue::False;
    }

    public function mutate(Operator $op, GoValue $rhs): never
    {
        throw new \BadMethodCallException('cannot operate');
    }

    /**
     * @return GoValue[]
     */
    public function unwrap(): array
    {
        return $this->values->array;
    }

    public function type(): SliceType
    {
        return $this->type;
    }

    public function copy(): static
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
            $copies[] = $value;
        }

        $this->cap *= 2;
        $this->pos = 0;
        $this->values = new UnderlyingArray($copies);
    }

    private function accessUnderlyingArray(): array
    {
        return \array_slice(
            $this->values->array,
            $this->pos,
            $this->len - $this->pos
        );
    }
}
