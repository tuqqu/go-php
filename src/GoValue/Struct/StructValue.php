<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Struct;

use GoPhp\Env\EnvMap;
use GoPhp\Error\InternalError;
use GoPhp\Error\RuntimeError;
use GoPhp\GoType\StructType;
use GoPhp\GoValue\AddressableTrait;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Hashable;
use GoPhp\GoValue\PointerValue;
use GoPhp\Operator;

use function GoPhp\assert_map_key;
use function GoPhp\assert_values_compatible;
use function GoPhp\try_unwind;
use function implode;
use function sprintf;

/**
 * @template-implements Hashable<string>
 * @template-implements AddressableValue<never>
 */
final class StructValue implements Hashable, AddressableValue
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

        return sprintf('{%s}', implode(' ', $str));
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
        assert_values_compatible($this, $rhs);

        $rhs = try_unwind($rhs);

        return match ($op) {
            Operator::EqEq => $this->equals($rhs),
            Operator::NotEq => $this->equals($rhs)->invert(),
            default => throw RuntimeError::undefinedOperator($op, $this),
        };
    }

    public function mutate(Operator $op, GoValue $rhs): void
    {
        if ($op === Operator::Eq) {
            assert_values_compatible($this, $rhs);

            /** @var self $rhs */
            $rhs = try_unwind($rhs);

            $this->fields = $rhs->fields->copy();

            return;
        }

        throw RuntimeError::undefinedOperator($op, $this);
    }

    public function unwrap(): never
    {
        throw InternalError::unreachable($this);
    }

    public function type(): StructType
    {
        return $this->type;
    }

    public function copy(): self
    {
        return new self($this->fields->copy(), $this->type);
    }

    public function accessField(string $name): GoValue
    {
        $field = $this->fields->tryGet($name);

        if ($field === null) {
            foreach ($this->type->promotedNames as $promotedName) {
                $field = $this->fields->get($promotedName);
                /** @var StructValue $promotedField */
                $promotedField = try_unwind($field->unwrap());

                if ($promotedField->type->hasField($name)) {
                    return $promotedField->accessField($name);
                }
            }
        }

        if ($field === null) {
            throw RuntimeError::redeclaredName($name);
        }

        return $field->unwrap();
    }

    private function equals(self $rhs): BoolValue
    {
        foreach ($this->fields->iter() as $field => $envValue) {
            $rhsEnvValue = $rhs->fields->get($field);

            if (!$envValue->equals($rhsEnvValue)) {
                return BoolValue::false();
            }
        }

        return BoolValue::true();
    }

    public function hash(): string
    {
        $hash = self::NAME;

        foreach ($this->fields->iter() as $field => $envValue) {
            $value = $envValue->unwrap();
            assert_map_key($value);

            $hash .= sprintf(':%s:%s', $field, $value->hash());
        }

        return $hash;
    }

    public function getFields(): EnvMap
    {
        return $this->fields;
    }
}
