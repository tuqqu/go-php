<?php

declare(strict_types=1);

namespace GoPhp\Env\Error;

final class UndefinedValueError extends EnvError
{
    public function __construct(string $name)
    {
        parent::__construct(\sprintf('Cannot access an undefined value with name "%s"', $name));
    }
}
