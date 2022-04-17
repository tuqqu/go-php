<?php

declare(strict_types=1);

namespace GoPhp\Stream;

final class StringStreamProvider implements StreamProvider
{
    private readonly OutputStream $stdout;
    private readonly OutputStream $stderr;
    private readonly InputStream $stdin;

    public function __construct(
        string &$outBuffer,
        string &$errBuffer,
        string $inputSource,
    ) {
        $this->stdout = new StringOutputStream($outBuffer);
        $this->stderr = new StringOutputStream($errBuffer);
        $this->stdin = new StringInputStream($inputSource);
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
