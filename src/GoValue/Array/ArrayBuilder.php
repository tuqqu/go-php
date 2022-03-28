<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Array;

use GoPhp\GoType\ArrayType;
use GoPhp\GoValue\GoValue;

final class ArrayBuilder
{
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
        if (!$this->type->internalType->conforms($value->type())) {
            throw new \Exception('wrong type');
        }

        $this->values[] = $value;
    }

    public function build(): ArrayValue
    {
        return new ArrayValue($this->values, $this->type);
    }
}
