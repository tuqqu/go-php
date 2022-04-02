<?php

declare(strict_types=1);

namespace GoPhp\Env\EnvValue;

use GoPhp\GoType\BasicType;
use GoPhp\GoType\ValueType;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\SimpleNumber;
use function GoPhp\assert_types_compatible;

abstract class EnvValue
{
    protected GoValue $value;

    public function __construct(
        public readonly string $name,
        public readonly ValueType $type,
        GoValue $value,
    ) {
        if ($value instanceof SimpleNumber && $type->isTyped()) {
            /** @var BasicType $type */
            $value = $value->convertTo($type);
        }

        assert_types_compatible($this->type, $value->type());

        $this->value = $value;
    }

    public function unwrap(): GoValue
    {
        return $this->value;
    }

    public function getType(): ValueType
    {
        return $this->type;
    }
}
