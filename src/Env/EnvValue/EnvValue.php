<?php

declare(strict_types=1);

namespace GoPhp\Env\EnvValue;

use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\ValueType;

abstract class EnvValue
{
    public function __construct(
        public readonly string $name,
        public GoValue $value,
    ) {
        static::validate($value);
    }

    public function unwrap(): GoValue
    {
        return $this->value;
    }

    public function getType(): ValueType
    {
        return $this->value->type();
    }

    // fixme
    protected static function validate(GoValue $value): void
    {
    }
}
