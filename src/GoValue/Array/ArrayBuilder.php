<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Array;

use GoPhp\GoType\ArrayType;
use GoPhp\GoValue\GoValue;
use function GoPhp\assert_types_compatible_with_cast;

final class ArrayBuilder
{
    /** @var GoValue[] */
    private array $values = [];

    private function __construct(
        private readonly ArrayType $type,
    ) {}

    public static function fromType(ArrayType $type): self
    {
        return new self($type);
    }

    public function push(GoValue $value): void
    {
        assert_types_compatible_with_cast($this->type->elemType, $value);

        $this->values[] = $value;
    }

    public function build(): ArrayValue
    {
        return new ArrayValue($this->values, $this->type);
    }
}
