<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Map;

use GoPhp\Error\OperationError;
use GoPhp\GoType\MapType;
use GoPhp\GoValue\AddressValue;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\NamedTrait;
use GoPhp\Operator;

use function GoPhp\assert_index_type;
use function GoPhp\assert_nil_comparison;
use function GoPhp\assert_types_compatible;

/**
 * @template K of GoValue
 * @template V of GoValue
 * @template-implements Map<K, V|MapLookupValue<V>>
 */
final class MapValue implements Map, GoValue
{
    use NamedTrait;

    public const NAME = 'map';

    //fixme add nil

    /**
     * @param Map<K, V> $innerMap
     */
    public function __construct(
        private Map $innerMap,
        private readonly MapType $type,
    ) {}

    public function toString(): string
    {
        $str = [];
        foreach ($this->iter() as $key => $value) {
            $str[] = \sprintf(
                '%s:%s',
                $key->toString(),
                $value->toString()
            );
        }

        return \sprintf('map[%s]', \implode(' ', $str));
    }

    public function get(GoValue $at): MapLookupValue
    {
        assert_index_type($at, $this->type->keyType, self::NAME);

        if (!$this->innerMap->has($at)) {
            /** @var V $defaultValue */
            $defaultValue = $this->type->elemType->defaultValue();

            return new MapLookupValue(
                $defaultValue,
                BoolValue::false(),
                function () use ($defaultValue, $at): void {
                    $this->set($defaultValue, $at);
                },
            );
        }

        return new MapLookupValue(
            $this->innerMap->get($at),
            BoolValue::true(),
        );
    }

    public function set(GoValue $value, GoValue $at): void
    {
        $this->innerMap->set($value, $at);
    }

    public function delete(GoValue $at): void
    {
        assert_index_type($at, $this->type->keyType, self::NAME);

        $this->innerMap->delete($at);
    }

    public function has(GoValue $at): bool
    {
        assert_index_type($at, $this->type->keyType, self::NAME);

        return $this->innerMap->has($at);
    }

    public function len(): int
    {
        return $this->innerMap->len();
    }

    public function iter(): iterable
    {
        yield from $this->innerMap->iter();
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
            /** @var self<K, V> $rhs */
            $this->innerMap = $rhs->innerMap;
            return;
        }

        throw OperationError::undefinedOperator($op, $this);
    }

    public function unwrap(): self
    {
        return $this;
    }

    public function type(): MapType
    {
        return $this->type;
    }

    public function copy(): self
    {
        return $this;
    }

    public function clone(): self
    {
        return clone $this;
    }
}
