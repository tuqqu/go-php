<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Map;

use GoPhp\GoType\ArrayType;
use GoPhp\GoType\BasicType;
use GoPhp\GoType\MapType;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\NonRefValue;
use function GoPhp\assert_index_type;
use function GoPhp\assert_types_compatible;

final class MapBuilder
{
    public const NAME = 'map';

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
        assert_index_type($at, $this->type->keyType, self::NAME);
        assert_types_compatible($this->type->elemType, $value->type());

        $this->internalMap->set($value, $at);
    }

    public function build(): MapValue
    {
        return new MapValue($this->internalMap, $this->type);
    }

    private function internalMapByType(): Map
    {
        switch (true) {
            // fixme add types
            // fixme || $this->type->keyType instanceof ArrayType
            case $this->type->keyType instanceof BasicType:
                /** @var NonRefValue $default */
                $default = $this->type->keyType->defaultValue();
                return new NonRefKeyMap($default::create(...));
            // fixme ass ref
            default:
                throw new \Exception(\sprintf('invalid map key type %s', $this->type->keyType->name()));
        };
    }
}
