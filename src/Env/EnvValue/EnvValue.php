<?php

declare(strict_types=1);

namespace GoPhp\Env\EnvValue;

use GoPhp\GoType\ValueType;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\SimpleNumber;

abstract class EnvValue
{
    public GoValue $value;

    public function __construct(
        public readonly string $name,
        public readonly ValueType $type, //fixme add mutable, remove others
        GoValue $value,
    ) {
        if ($value instanceof SimpleNumber && $this->type->isTyped()) {
            $value = SimpleNumber::createFrom($this->type, $value->unwrap()); //fixme type
        }

        if (!$this->type->conforms($value->type())) {
            throw new \Exception('type error');
        }

        static::validate($value);

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

    // fixme
    protected static function validate(GoValue $value): void
    {
    }
}
