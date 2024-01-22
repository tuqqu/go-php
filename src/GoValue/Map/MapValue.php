<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Map;

use GoPhp\Error\InternalError;
use GoPhp\Error\PanicError;
use GoPhp\Error\RuntimeError;
use GoPhp\GoType\MapType;
use GoPhp\GoValue\AddressableTrait;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\Hashable;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\PointerValue;
use GoPhp\GoValue\UntypedNilValue;
use GoPhp\Operator;

use function implode;
use function sprintf;
use function GoPhp\assert_map_key;
use function GoPhp\assert_index_type;
use function GoPhp\assert_nil_comparison;
use function GoPhp\assert_types_compatible;

use const GoPhp\GoValue\NIL;

/**
 * @template K of Hashable&GoValue
 * @template V of GoValue
 *
 * @template-implements Map<K, V|MapLookupValue<V>>
 * @template-implements AddressableValue<never>
 *
 * psalm bug with Intersection types in generics
 * @psalm-suppress PossiblyUndefinedMethod
 */
final class MapValue implements Map, AddressableValue
{
    use AddressableTrait;

    public const string NAME = 'map';

    /**
     * @param Map<K, V>|null $innerMap
     */
    public function __construct(
        private ?Map $innerMap,
        private readonly MapType $type,
    ) {
        assert_map_key($type->keyType->zeroValue());
    }

    public static function nil(MapType $type): self
    {
        return new self(NIL, $type);
    }

    public function toString(): string
    {
        $str = [];
        foreach ($this->iter() as $key => $value) {
            $str[] = sprintf(
                '%s:%s',
                $key->toString(),
                $value->toString()
            );
        }

        return sprintf('map[%s]', implode(' ', $str));
    }

    public function get(GoValue $at): MapLookupValue
    {
        assert_index_type($at, $this->type->keyType, self::NAME);

        if (!$this->innerMap?->has($at)) {
            /** @var V $defaultValue */
            $defaultValue = $this->type->elemType->zeroValue();

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

        /** @var V $value */
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

        throw RuntimeError::undefinedOperator($op, $this, true);
    }

    public function operateOn(Operator $op, GoValue $rhs): BoolValue
    {
        assert_nil_comparison($this, $rhs, self::NAME);

        return match ($op) {
            Operator::EqEq => new BoolValue($this->innerMap === NIL),
            Operator::NotEq => new BoolValue($this->innerMap !== NIL),
            default => throw RuntimeError::undefinedOperator($op, $this),
        };
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

        throw RuntimeError::undefinedOperator($op, $this);
    }

    public function unwrap(): never
    {
        throw InternalError::unreachable($this);
    }

    public function type(): MapType
    {
        return $this->type;
    }

    public function copy(): self
    {
        return $this;
    }
}
