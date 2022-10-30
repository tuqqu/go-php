<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\GoType\Converter\DefaultConverter;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Struct\StructBuilder;
use GoPhp\GoValue\Struct\StructValue;

use function GoPhp\normalize_unwindable;

final class StructType implements GoType
{
    /**
     * @param array<string, GoType> $fields
     */
    public function __construct(
        public readonly array $fields,
    ) {}

    public function name(): string
    {
        $fields = [];
        foreach ($this->fields as $field => $type) {
            $fields[] = \sprintf('%s %s', $field, $type->name());
        }

        return \sprintf(
            'struct{%s}',
            \implode(', ', $fields),
        );
    }

    public function equals(GoType $other): bool
    {
        $other = normalize_unwindable($other);

        if (!$other instanceof self) {
            return false;
        }

        if (\count($this->fields) !== \count($other->fields)) {
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

    public function reify(): self
    {
        return $this;
    }

    public function defaultValue(): StructValue
    {
        return StructBuilder::fromType($this)->build();
    }

    public function convert(GoValue $value): GoValue
    {
        return DefaultConverter::convert($value, $this);
    }
}
