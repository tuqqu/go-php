<?php

declare(strict_types=1);

namespace GoPhp\Env\EnvValue;

use GoPhp\GoType\ValueType;
use GoPhp\GoValue\GoValue;

abstract class EnvValue
{
    public function __construct(
        public readonly string $name,
        public GoValue $value,
        public readonly ValueType $type, //fixme add mutable, remove others
    ) {
        if (!$this->type->conforms($this->value->type())) {
            throw new \Exception('type error');
        }
        static::validate($value);
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
