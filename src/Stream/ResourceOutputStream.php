<?php

declare(strict_types=1);

namespace GoPhp\Stream;

class ResourceOutputStream implements OutputStream
{
    /**
     * @param resource $resource
     */
    public function __construct(
        private mixed $resource
    ) {}

    public function write(string $str): void
    {
        \fwrite($this->resource, $str);
    }

    public function writeln(string $str): void
    {
        $this->write($str . "\n");
    }
}
