<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\GoType\Converter\DefaultConverter;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\Struct\StructBuilder;
use GoPhp\GoValue\Struct\StructValue;

use function count;
use function implode;
use function sprintf;
use function GoPhp\try_unwind;

/**
 * @see https://golang.org/ref/spec#Struct_types
 */
final class StructType implements GoType
{
    /**
     * @param array<string, GoType> $fields
     * @param list<string> $promotedNames
     */
    public function __construct(
        public readonly array $fields,
        public readonly array $promotedNames,
    ) {}

    public function name(): string
    {
        $fields = [];
        foreach ($this->fields as $field => $type) {
            $fields[] = sprintf('%s %s', $field, $type->name());
        }

        return sprintf(
            '%s{%s}',
            StructValue::NAME,
            implode(', ', $fields),
        );
    }

    public function equals(GoType $other): bool
    {
        $other = try_unwind($other);

        if (!$other instanceof self) {
            return false;
        }

        if (count($this->fields) !== count($other->fields)) {
            return false;
        }

        foreach ($this->fields as $field => $typeA) {
            $typeB = $other->fields[$field] ?? null;
            $equals = (bool) $typeB?->equals($typeA);

            if (!$equals) {
                return false;
            }
        }

        return true;
    }

    public function isCompatible(GoType $other): bool
    {
        return $this->equals($other);
    }

    public function zeroValue(): StructValue
    {
        return StructBuilder::fromType($this)->build();
    }

    public function convert(AddressableValue $value): AddressableValue
    {
        return DefaultConverter::convert($value, $this);
    }

    public function hasField(string $name): bool
    {
        return isset($this->fields[$name]);
    }
}
