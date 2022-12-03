<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Array;

use GoParser\Ast\KeyedElement;
use GoPhp\CompositeValueBuilder;
use GoPhp\GoType\ArrayType;
use GoPhp\GoValue\AddressableValue;

use function GoPhp\assert_types_compatible_with_cast;

final class ArrayBuilder implements CompositeValueBuilder
{
    /** @var AddressableValue[] */
    private array $values = [];

    private function __construct(
        private readonly ArrayType $type,
    ) {}

    public static function fromType(ArrayType $type): self
    {
        return new self($type);
    }

    public function push(KeyedElement $element, callable $evaluator): void
    {
        $value = $evaluator($element->element);

        assert_types_compatible_with_cast($this->type->elemType, $value);

        $this->values[] = $value;
    }

    public function build(): ArrayValue
    {
        return new ArrayValue($this->values, $this->type);
    }
}
