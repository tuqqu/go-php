<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Struct;

use GoPhp\Env\EnvMap;
use GoPhp\Error\OperationError;
use GoPhp\GoType\StructType;
use GoPhp\GoValue\AddressableTrait;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\PointerValue;
use GoPhp\Operator;

use function GoPhp\assert_values_compatible;
use function GoPhp\normalize_value;

final class StructValue implements AddressableValue
{
    use AddressableTrait;

    public const NAME = 'struct';

    public function __construct(
        private EnvMap $fields,
        private readonly StructType $type,
    ) {}

    public function toString(): string
    {
        $str = [];
        foreach ($this->fields->iter() as $value) {
            $str[] = $value->unwrap()->toString();
        }

        return \sprintf('{%s}', \implode(' ', $str));
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
        throw OperationError::undefinedOperator($op, $this);
    }

    public function equals(GoValue $rhs): BoolValue
    {
        return BoolValue::false();
    }

    public function mutate(Operator $op, GoValue $rhs): void
    {
        if ($op === Operator::Eq) {
            assert_values_compatible($this, $rhs);

            /** @var self $rhs */
            $rhs = normalize_value($rhs);

            $this->fields = $rhs->fields->copy();

            return;
        }

        throw OperationError::undefinedOperator($op, $this);
    }

    public function unwrap(): self
    {
        return $this;
    }

    public function type(): StructType
    {
        return $this->type;
    }

    public function copy(): self
    {
        return new self($this->fields->copy(), $this->type);
    }

    public function clone(): self
    {
        return clone $this;
    }

    public function accessField(string $name): GoValue
    {
        return $this->fields->get($name)->unwrap();
    }
}
