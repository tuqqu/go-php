<?php

declare(strict_types=1);

namespace GoPhp\Env\EnvValue;

use GoPhp\GoType\GoType;
use GoPhp\GoType\UntypedNilType;
use GoPhp\GoValue\GoValue;

final class MutableValue extends EnvValue
{
    public function __construct(string $name, GoType $type, GoValue $value)
    {
        if ($type instanceof UntypedNilType) {
            throw new \Exception('use of untyped nil in variable declaration');
        }

        parent::__construct($name, $type, $value);
    }
}
