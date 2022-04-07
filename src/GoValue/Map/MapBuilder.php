<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Map;

use GoPhp\GoType\MapType;
use GoPhp\GoValue\GoValue;
use function GoPhp\assert_index_type;
use function GoPhp\assert_types_compatible;

final class MapBuilder
{
    public const NAME = 'map';
    private readonly \SplObjectStorage $values;

    private function __construct(
        private readonly MapType $type,
    ) {
        $this->values = new \SplObjectStorage();
    }

    public static function fromType(MapType $type): self
    {
        return new self($type);
    }

    public function set(GoValue $value, GoValue $key): void
    {
        assert_index_type($key, $this->type->keyType, self::NAME);
        assert_types_compatible($this->type->elemType, $value->type());

        $this->values->attach($value, $key);
    }

    public function build(): MapValue
    {
        return new MapValue($this->values, $this->type);
    }
}
