<?php

declare(strict_types=1);

namespace GoPhp\ErrorHandler;

use GoPhp\Error\GoError;

interface ErrorHandler
{
    public function onError(GoError $error): void;
}
