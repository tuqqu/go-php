<?php

declare(strict_types=1);

namespace GoPhp;

use const STDOUT;
use const STDERR;
use const STDIN;

final class StdStreamProvider implements StreamProvider
{
    public function stdout(): mixed
    {
        return STDOUT;
    }

    public function stderr(): mixed
    {
        return STDERR;
    }

    public function stdin(): mixed
    {
        return STDIN;
    }
}
