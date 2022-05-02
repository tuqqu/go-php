<?php

declare(strict_types=1);

namespace GoPhp\Error;

use GoPhp\GoValue\Func\Param;
use GoPhp\GoValue\Func\Params;
use GoPhp\GoValue\GoValue;

final class ProgramError extends \LogicException
{
    public static function wrongBuiltinArgumentNumber(int|string $expected, int $actual): self
    {
        $msg = $expected > $actual ?
            'not enough arguments in call (expected %s, found %d)' :
            'too many arguments in call (expected %s, found %d)';

        return new self(\sprintf($msg, $expected, $actual));
    }

    public static function wrongFuncArgumentNumber(array $actualArgv, Params $params): self
    {
        return self::wrongFuncArity($actualArgv, $params, 'arguments in call');
    }

    public static function wrongReturnValueNumber(array $actualArgv, Params $params): self
    {
        return self::wrongFuncArity($actualArgv, $params, 'return values');
    }

    private static function wrongFuncArity(
        array $actualArgv,
        Params $params,
        string $type,
    ): self {
        $msg = $params->len > \count($actualArgv) ?
            'not enough ' :
            'too many ';

        $msg .= \sprintf(
            "%s\nhave (%s)\nwant (%s)",
            $type,
            \implode(', ', \array_map(
                static fn (GoValue $value): string => $value->type()->name(),
                $actualArgv,
            )),
            $params,
        );

        return new self($msg);
    }

    public static function jumpBeforeDecl(): self
    {
        return new self('goto jumps over declaration');
    }
}
