<?php

declare(strict_types=1);

namespace GoPhp\Error;

final class ValueError extends \RuntimeException
{
    public static function multipleValueInSingleContext(): self
    {
        return new self('Multiple-value in single-value context');
    }
}
