<?php

declare(strict_types=1);

namespace GoPhp\ErrorHandler;

/**
 * Error handler that does nothing.
 */
final class Noop implements ErrorHandler
{
    public function onError(string|\Stringable $error): void {}
}
