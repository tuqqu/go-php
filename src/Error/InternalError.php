<?php

declare(strict_types=1);

namespace GoPhp\Error;

use GoParser\Ast\Stmt\Stmt;

final class InternalError extends \LogicException
{
    public static function unreachableMethodCall(): self
    {
        return new self('unreachable method call');
    }

    public static function unknownStatement(Stmt $stmt): self
    {
        return new self(\sprintf('unknown statement %s', $stmt::class));
    }
}
