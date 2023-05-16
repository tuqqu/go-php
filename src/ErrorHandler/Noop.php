<?php

declare(strict_types=1);

namespace GoPhp\ErrorHandler;

use GoPhp\Error\GoError;

/**
 * Error handler that does nothing.
 */
final class Noop implements ErrorHandler
{
    public function onError(GoError $error): void {}
}
