<?php

declare(strict_types=1);

namespace GoPhp\Stream;

final class StdStreamProvider implements StreamProvider
{
    private readonly OutputStream $stdout;
    private readonly OutputStream $stderr;
    private readonly InputStream $stdin;

    public function __construct()
    {
        $this->stdout = new ResourceOutputStream(\STDOUT);
        $this->stderr = new ResourceOutputStream(\STDERR);
        $this->stdin = new ResourceInputStream(\STDIN);
    }

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
