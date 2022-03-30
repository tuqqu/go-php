<?php

declare(strict_types=1);

namespace GoPhp\Error;

final class DefinitionError extends \LogicException
{
    public static function undefinedArrayKey(int $key)
    {
        return new self(\sprintf('Undefined array key %d', $key));
    }
}
