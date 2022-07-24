<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Struct;

use GoPhp\Env\ValueTable;
use GoPhp\Error\OperationError;
use GoPhp\GoType\StructType;
use GoPhp\GoValue\AddressValue;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\NamedTrait;
use GoPhp\Operator;

use function GoPhp\assert_nil_comparison;

final class StructValue implements GoValue
{
    use NamedTrait;

    public const NAME = 'struct';

    //fixme add nil

    public function __construct(
        private readonly ValueTable $fields,
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

    public function operate(Operator $op): AddressValue
    {
        if ($op === Operator::BitAnd) {
            return new AddressValue($this);
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
//        if ($op === Operator::Eq) {
//            assert_types_compatible($this->type, $rhs->type());
        // fixme
//        }

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

    public function copy(): static
    {
        return $this;
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
