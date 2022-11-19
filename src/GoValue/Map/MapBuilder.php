<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Map;

use GoParser\Ast\KeyedElement;
use GoPhp\CompositeValueBuilder;
use GoPhp\GoType\MapType;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Hashable;

use function GoPhp\assert_index_type;
use function GoPhp\assert_types_compatible_with_cast;

final class MapBuilder implements CompositeValueBuilder
{
    private function __construct(
        private readonly MapType $type,
        private readonly Map $innerMap = new KeyValueTupleMap(),
    ) {
    }

    public static function fromType(MapType $type): self
    {
        return new self($type);
    }

    public function push(KeyedElement $element, callable $evaluator): void
    {
        /** @var Hashable&GoValue $pos */
        $pos = $evaluator($element->key);
        $value = $evaluator($element->element)->copy();

        assert_index_type($pos, $this->type->keyType, MapValue::NAME);
        assert_types_compatible_with_cast($this->type->elemType, $value);

        $this->innerMap->set($value, $pos);
    }

    public function build(): MapValue
    {
        return new MapValue($this->innerMap, $this->type);
    }
}
