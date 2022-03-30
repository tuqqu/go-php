<?php

declare(strict_types=1);

namespace GoPhp\Stream;

interface InputStream
{
    public function getChar(): string;

    public function getLine(): string;
}
