<?php

declare(strict_types=1);

namespace GoPhp\Stream;

use function fgetc;
use function fgets;

class ResourceInputStream implements InputStream
{
    /**
     * @param resource $stream
     */
    public function __construct(
        private readonly mixed $stream
    ) {}

    public function getChar(): ?string
    {
        $char = fgetc($this->stream);

        return $char === false ? null : $char;
    }

    public function getLine(): ?string
    {
        $char = fgets($this->stream);

        return $char === false ? null : $char;
    }
}
