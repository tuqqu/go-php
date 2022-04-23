<?php

declare(strict_types=1);

namespace GoPhp\Env\Error;

final class UndefinedTypeError extends EnvError
{
    public function __construct(string $name)
    {
        parent::__construct(\sprintf('Type %s is not defined', $name));
    }
}
