<?php

declare(strict_types=1);

namespace GoPhp\Stream;

interface OutputStream
{
    public function write(string $str): void;

    public function writeln(string $str): void;
}
