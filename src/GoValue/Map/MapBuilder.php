<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Map;

use GoPhp\Error\RuntimeError;
use GoPhp\GoType\BasicType;
use GoPhp\GoType\MapType;
use GoPhp\GoType\RefType;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\NonRefValue;

use function GoPhp\assert_index_type;
use function GoPhp\assert_types_compatible_with_cast;

final class MapBuilder
{
    private readonly Map $internalMap;

    private function __construct(
        private readonly MapType $type,
    ) {
        $this->internalMap = $this->internalMapByType();
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

        $this->internalMap->set($value, $at);
    }

    public function build(): MapValue
    {
        return new MapValue($this->internalMap, $this->type);
    }

    private function internalMapByType(): Map
    {
        if ($this->type->keyType instanceof RefType) {
            throw RuntimeError::invalidMapKeyType($this->type->keyType);
        }

        if ($this->type->keyType instanceof BasicType) {
            /** @var NonRefValue $default */
            $default = $this->type->keyType->defaultValue();

            return new NonRefKeyMap($default::create(...));
        }

        // fixme
        return new RefKeyMap();
    }
}
