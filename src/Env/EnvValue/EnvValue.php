<?php

declare(strict_types=1);

namespace GoPhp\Env\EnvValue;

use GoPhp\GoType\NamedType;
use GoPhp\GoType\GoType;
use GoPhp\GoType\UntypedType;
use GoPhp\GoType\WrappedType;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\SimpleNumber;

use GoPhp\GoValue\TypeValue;

use function GoPhp\assert_types_compatible;

abstract class EnvValue
{
    protected GoValue $value;

    public function __construct(
        public readonly string $name,
        public readonly GoType $type, // fixme maybe make optional
        GoValue $value,
    ) {
        $value = static::convertIfNeeded($value, $type);

        assert_types_compatible($type, $value->type());

        $this->value = $value;
    }

    public function unwrap(): GoValue
    {
        return $this->value;
    }

    public function getType(): GoType
    {
        return $this->type;
    }

    protected static function convertIfNeeded(GoValue $value, GoType $type): GoValue
    {
        switch (true) {
            case $value instanceof SimpleNumber
                && $type instanceof NamedType
                && $value->type() instanceof UntypedType:
                $value = $value->becomeTyped($type);
                break;
            case $type instanceof WrappedType
                && !$value instanceof TypeValue:
                $value = $type->convert($value);
                break;
            default:
        }

        return $value;
    }
}
