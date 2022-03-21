<?php

declare(strict_types=1);

namespace GoPhp\Env\Error;

final class AlreadyDefinedError extends EnvError
{
    public function __construct(string $name)
    {
        parent::__construct(\sprintf('Value with name "%s" is already defined', $name));
    }
}
