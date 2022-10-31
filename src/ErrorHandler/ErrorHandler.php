<?php

declare(strict_types=1);

namespace GoPhp\ErrorHandler;

interface ErrorHandler
{
    // fixme change to interface
    public function onError(string $error): void;
}
