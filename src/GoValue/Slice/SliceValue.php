<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Slice;

use GoPhp\Error\OperationError;
use GoPhp\GoType\SliceType;
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

    private int $len;

    /**
     * @param GoValue[] $values
     */
    public function __construct(
        public array $values,
        public readonly SliceType $type,
    ) {
        $this->len = \count($this->values);
    }

    public function toString(): string
    {
        $str = [];
        foreach ($this->values as $value) {
            $str[] = $value->toString();
        }

        return \sprintf('[%s]', \implode(' ', $str));
    }

    public function get(GoValue $at): GoValue
    {
        assert_index_value($at, BaseIntValue::class, self::NAME);
        assert_index_exists($int = $at->unwrap(), $this->len);

        return $this->values[$int];
    }

    public function set(GoValue $value, GoValue $at): void
    {
        assert_index_value($at, BaseIntValue::class, self::NAME);
        assert_index_exists($int = $at->unwrap(), $this->len);
        assert_types_compatible($value->type(), $this->type->internalType);

        $this->values[$int] = $value;
    }

    public function len(): int
    {
        return $this->len;
    }

    /**
     * @return iterable<UntypedIntValue, GoValue>
     */
    public function iter(): iterable
    {
        foreach ($this->values as $key => $value) {
            yield new UntypedIntValue($key) => $value;
        }
    }

    public function append(GoValue $value): void
    {
        assert_types_compatible($value->type(), $this->type->internalType);

        $this->values[] = $value;
        ++$this->len;
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
        return $this->values;
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
        return clone $this;
    }
}
