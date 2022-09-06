<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Map;

use GoPhp\Error\OperationError;
use GoPhp\Error\PanicError;
use GoPhp\GoType\MapType;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\UntypedNilValue;
use GoPhp\GoValue\PointerValue;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\AddressableTrait;
use GoPhp\Operator;

use function GoPhp\assert_index_type;
use function GoPhp\assert_nil_comparison;
use function GoPhp\assert_types_compatible;

use const GoPhp\NIL;

/**
 * @template K of GoValue
 * @template V of GoValue
 * @template-implements Map<K, V|MapLookupValue<V>>
 */
final class MapValue implements Map, AddressableValue
{
    use AddressableTrait;

    public const NAME = 'map';

    /**
     * @param Map<K, V>|null $innerMap
     */
    public function __construct(
        private ?Map $innerMap,
        private readonly MapType $type,
    ) {}

    public static function nil(MapType $type): self
    {
        return new self(NIL, $type);
    }

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

        if (!$this->innerMap?->has($at)) {
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
        if ($this->innerMap === NIL) {
            throw PanicError::nilMapAssignment();
        }

        $this->innerMap->set($value, $at);
    }

    public function delete(GoValue $at): void
    {
        assert_index_type($at, $this->type->keyType, self::NAME);

        $this->innerMap?->delete($at);
    }

    public function has(GoValue $at): bool
    {
        assert_index_type($at, $this->type->keyType, self::NAME);

        return $this->innerMap?->has($at) ?? false;
    }

    public function len(): int
    {
        return $this->innerMap?->len() ?? 0;
    }

    public function iter(): iterable
    {
        yield from $this->innerMap?->iter() ?? [];
    }

    public function operate(Operator $op): PointerValue
    {
        if ($op === Operator::BitAnd) {
            return PointerValue::fromValue($this);
        }

        throw OperationError::undefinedOperator($op, $this, true);
    }

    public function operateOn(Operator $op, GoValue $rhs): BoolValue
    {
        assert_nil_comparison($this, $rhs, self::NAME);

        return match ($op) {
            Operator::EqEq => new BoolValue($this->innerMap === null),
            Operator::NotEq => new BoolValue($this->innerMap !== null),
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
            if ($rhs instanceof UntypedNilValue) {
                $this->innerMap = NIL;

                return;
            }

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
