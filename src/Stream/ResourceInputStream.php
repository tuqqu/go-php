<?php

declare(strict_types=1);

namespace GoPhp\Stream;

class ResourceInputStream implements InputStream
{
    /**
     * @param resource $resource
     */
    public function __construct(
        private mixed $resource
    ) {}

    public function getChar(): ?string
    {
        $char = \fgetc($this->resource);

        return $char === false ?
            null :
            $char;
    }

    public function getLine(): ?string
    {
        $char = \fgets($this->resource);

        return $char === false ?
            null :
            $char;
    }
}
