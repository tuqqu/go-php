<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Map;

use GoPhp\GoType\MapType;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Hashable;

use function GoPhp\assert_index_type;
use function GoPhp\assert_types_compatible_with_cast;

final class MapBuilder
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

    public function set(GoValue $value, Hashable&GoValue $at): void
    {
        $value = $value->copy();

        assert_index_type($at, $this->type->keyType, MapValue::NAME);
        assert_types_compatible_with_cast($this->type->elemType, $value);

        $this->innerMap->set($value, $at);
    }

    public function build(): MapValue
    {
        return new MapValue($this->innerMap, $this->type);
    }
}
