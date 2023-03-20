<?php

declare(strict_types=1);

namespace GoPhp\Stream;

use function fwrite;

class ResourceOutputStream implements OutputStream
{
    /**
     * @param resource $stream
     */
    public function __construct(
        private readonly mixed $stream
    ) {}

    public function write(string $str): void
    {
        fwrite($this->stream, $str);
    }

    public function writeln(string $str): void
    {
        $this->write($str . "\n");
    }
}
