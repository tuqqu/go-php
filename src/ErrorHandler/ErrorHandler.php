<?php

declare(strict_types=1);

namespace GoPhp\ErrorHandler;

interface ErrorHandler
{
    public function onError(string|\Stringable $error): void;
}
