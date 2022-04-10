<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Map;

use GoPhp\Error\OperationError;
use GoPhp\GoType\MapType;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Sequence;
use GoPhp\Operator;
use function GoPhp\assert_index_type;
use function GoPhp\assert_nil_comparison;
use function GoPhp\assert_types_compatible;

final class MapValue implements Sequence, GoValue
{
    public const NAME = 'map';

    public function __construct(
        private readonly Map $map,
        public readonly MapType $type,
    ) {}

    public function toString(): string
    {
        $str = [];
        foreach ($this->iter() as $key => $value) {
            $str[] = \sprintf(
                '%s:%s',
                $key instanceof GoValue ?
                    $key->toString() :
                    $key,
                $value->toString()
            );
        }

        return \sprintf('map[%s]', \implode(' ', $str));
    }

    public function get(GoValue $at): GoValue
    {
        assert_index_type($at, $this->type->keyType, self::NAME);

        return $this->map->has($at) ?
            $this->map->get($at) :
            $this->type->elemType->defaultValue(); //fixme prob set here as well
    }

    public function set(GoValue $value, GoValue $at): void
    {
        assert_index_type($at, $this->type->keyType, self::NAME);
        assert_types_compatible($value->type(), $this->type->elemType);

        $this->map->set($value, $at);
    }

    public function len(): int
    {
        return $this->map->len();
    }

    public function iter(): iterable
    {
        yield from $this->map->iter();
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

    public function unwrap(): self
    {
        return $this;
    }

    public function type(): MapType
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
