<?php

declare(strict_types=1);

namespace GoPhp\Env\EnvValue;

use GoPhp\GoType\GoType;
use GoPhp\GoType\UntypedNilType;
use GoPhp\GoValue\GoValue;
use function GoPhp\assert_types_compatible;

final class MutableValue extends EnvValue
{
    public function __construct(string $name, GoType $type, GoValue $value)
    {
        if ($type === UntypedNilType::Nil) {
            throw new \Exception('use of untyped nil in variable declaration');
        }

        parent::__construct($name, $type, $value);
    }

    public function set(GoValue $value): void
    {
        assert_types_compatible($this->getType(), $value->type());

        $this->value = self::convertIfNeeded($value, $this->type);
    }
}
