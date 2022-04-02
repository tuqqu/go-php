<?php

declare(strict_types=1);

namespace GoPhp\Env\Error;

final class CannotBeMutatedError extends EnvError
{
    public function __construct(string $name)
    {
        parent::__construct(\sprintf('Value with name "%s" cannot be assigned', $name));
    }
}
