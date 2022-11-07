<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Map;

use GoPhp\Error\RuntimeError;
use GoPhp\GoType\MapType;
use GoPhp\GoType\RefType;
use GoPhp\GoValue\GoValue;

use function GoPhp\assert_index_type;
use function GoPhp\assert_types_compatible_with_cast;

final class MapBuilder
{
    private function __construct(
        private readonly MapType $type,
        private readonly Map $innerMap = new KeyValueTupleMap(),
    ) {
        if ($this->type->keyType instanceof RefType) {
            throw RuntimeError::invalidMapKeyType($this->type->keyType);
        }
    }

    public static function fromType(MapType $type): self
    {
        return new self($type);
    }

    public function set(GoValue $value, GoValue $at): void
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
