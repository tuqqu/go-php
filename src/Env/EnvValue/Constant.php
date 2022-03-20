<?php

declare(strict_types=1);

namespace GoPhp\Env\EnvValue;

use GoPhp\GoValue\GoValue;

final class Constant extends EnvValue
{
    // fixme check scalars
    // BaseIntValue|BaseFloatValue|StringValue|BoolValue|RuneValue
    protected static function validateValue(GoValue $value): void
    {
    }
}
