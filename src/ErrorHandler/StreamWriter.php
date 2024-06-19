<?php

declare(strict_types=1);

namespace GoPhp\ErrorHandler;

use GoPhp\Error\GoError;
use GoPhp\Stream\OutputStream;

/**
 * Error handler that writes error messages to a stream.
 */
final class StreamWriter implements ErrorHandler
{
    public function __construct(
        private readonly OutputStream $stream,
    ) {}

    public function onError(GoError $error): void
    {
        $this->stream->writeln($error->getMessage());
    }
}
