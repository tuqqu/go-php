<?php

declare(strict_types=1);

namespace GoPhp\Stream;

class StringOutputStream implements OutputStream
{
    public function __construct(
        private string &$buffer
    ) {}

    public function write(string $str): void
    {
        $this->buffer .= $str;
    }

    public function writeln(string $str): void
    {
        $this->buffer .= $str . "\n";
    }
}
