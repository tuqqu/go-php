<?php

declare(strict_types=1);

namespace GoPhp\Env\EnvValue;

use GoPhp\GoType\NamedType;
use GoPhp\GoType\GoType;
use GoPhp\GoType\UntypedType;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\SimpleNumber;
use function GoPhp\assert_types_compatible;

abstract class EnvValue
{
    protected GoValue $value;

    public function __construct(
        public readonly string $name,
        public readonly GoType $type,
        GoValue $value,
    ) {
        //fixme think here of var x int = 44.4
        $value = static::convertIfNeeded($value, $type);

        //fixme remove
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
        /** @var NamedType $type */
        if (
            $value instanceof SimpleNumber
            && $type instanceof NamedType
            && $value->type() instanceof UntypedType
        ) {
            $value = $value->convertTo($type);
        }

        return $value;
    }
}
