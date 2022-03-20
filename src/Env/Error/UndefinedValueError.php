<?php

declare(strict_types=1);

namespace GoPhp\Error;

use GoPhp\Env\Error\EnvError;

final class UndefinedValueError extends EnvError
{
    public function __construct(string $name)
    {
        parent::__construct(\sprintf('Cannot access an undefined value with name "%s"', $name));
    }
}
