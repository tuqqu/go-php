<?php

declare(strict_types=1);

namespace GoPhp\Stream;

use const STDERR;
use const STDIN;
use const STDOUT;

final class StdStreamProvider implements StreamProvider
{
    public function __construct(
        private readonly OutputStream $stdout = new ResourceOutputStream(STDOUT),
        private readonly OutputStream $stderr = new ResourceOutputStream(STDERR),
        private readonly InputStream $stdin = new ResourceInputStream(STDIN),
    ) {}

    public function stdout(): OutputStream
    {
        return $this->stdout;
    }

    public function stderr(): OutputStream
    {
        return $this->stderr;
    }

    public function stdin(): InputStream
    {
        return $this->stdin;
    }
}
