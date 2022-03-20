<?php

declare(strict_types=1);

namespace GoPhp\Error;

use GoPhp\Env\Error\EnvError;

final class AlreadyDefinedError extends EnvError
{
    public function __construct(string $name)
    {
        parent::__construct(\sprintf('Value with name "%s" is already defined', $name));
    }
}
