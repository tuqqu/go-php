<?php

declare(strict_types=1);

namespace GoPhp\ErrorHandler;

use GoPhp\Stream\OutputStream;

/**
 * Error handler that outputs error messages to a given stream.
 */
final class OutputToStream implements ErrorHandler
{
    public function __construct(
        private readonly OutputStream $stream
    ) {}

    public function onError(string|\Stringable $error): void
    {
        $this->stream->writeln((string) $error);
    }
}
