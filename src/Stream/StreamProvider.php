<?php

declare(strict_types=1);

namespace GoPhp\Stream;

interface StreamProvider
{
    public function stdout(): OutputStream;

    public function stderr(): OutputStream;

    public function stdin(): InputStream;
}
