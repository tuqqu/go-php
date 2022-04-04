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

    private function __construct(
        private readonly MapType $type,
        private array $values,
    ) {}

    public static function fromType(MapType $type): self
    {
        return new self($type, []);
    }

    public static function fromValue(MapValue $value): self
    {
         return new self($value->type, $value->values);
    }

    public function set(GoValue $key, GoValue $value): void
    {
        assert_index_type($key, $this->type->keyType, self::NAME);
        assert_types_compatible($this->type->elemType, $value->type());

        $this->values[MapValue::keyify($key)] = $value;
    }

    public function build(): MapValue
    {
        return new MapValue($this->values, $this->type);
    }
}
