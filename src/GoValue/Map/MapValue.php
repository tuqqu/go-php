<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Map;

use GoPhp\GoType\MapType;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Sequence;
use GoPhp\GoValue\StringValue;
use GoPhp\Operator;
use GoPhp\StmtValue\SimpleValue;
use function GoPhp\assert_index_type;
use function GoPhp\assert_types_compatible;

final class MapValue implements Sequence, GoValue
{
    public const NAME = 'map';

    private int $len;

    /**
     * @param GoValue[] $values
     */
    public function __construct(
        public array $values,
        public readonly MapType $type,
    ) {
        $this->len = \count($this->values);
    }

    public static function keyify(GoValue $value): int|string
    {
        return match (true) {
            $value instanceof SimpleValue => $value->unwrap(),
            $value instanceof StringValue => $value->unwrap(),
            default => throw new \Exception('non-supported key value')
        };
    }

    public function toString(): string
    {
        $str = [];
        foreach ($this->values as $value) {
            $str[] = $value->toString();
        }

        return \sprintf('map[%s]', \implode(' ', $str));
    }

    public function get(GoValue $at): GoValue
    {
        assert_index_type($at, $this->type->keyType, self::NAME);

        return $this->values[self::keyify($at)] ?? $this->type->keyType->defaultValue(); //fixme prob set here as well
    }

    public function set(GoValue $value, GoValue $at): void
    {
        assert_index_type($at, $this->type->keyType, self::NAME);
        assert_types_compatible($value->type(), $this->type->elemType);

        $key = $at->unwrap();

        if (!isset($this->values[$key])) {
            ++$this->len;
        }

        $this->values[self::keyify($at)] = $value;
    }

    public function len(): int
    {
        return $this->len;
    }

    public function operate(Operator $op): self
    {
        throw new \BadMethodCallException(); //fixme
    }

    public function operateOn(Operator $op, GoValue $rhs): self
    {
        throw new \BadMethodCallException(); //fixme
    }

    public function equals(GoValue $rhs): BoolValue
    {
        throw new \BadMethodCallException(); //fixme
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
