<?php

declare(strict_types=1);

namespace GoPhp\Error;

use GoParser\Ast\Stmt\Stmt;
use GoPhp\GoValue\Func\Params;
use GoPhp\GoValue\GoValue;

final class ProgramError extends \LogicException
{
    public static function noEntryPoint(string $funcName): self
    {
        return new self(\sprintf('function %s is undeclared in the main package', $funcName));
    }

    public static function nonDeclarationOnTopLevel(): self
    {
        return new self('non-declaration statement outside function body');
    }

    public static function nestedFunction(): self
    {
        return new self('function declaration in a function scope');
    }



    public static function multipleDefaults(): self
    {
        return new self('multiple defaults');
    }

    public static function invalidArrayLength(): self
    {
        return new self('invalid array length');
    }

    public static function misplacedFallthrough(): self
    {
        return new self('fallthrough statement out of place');
    }

    public static function tooManyRangeVars(): self
    {
        return new self('range clause permits at most two iteration variables');
    }

    public static function redeclaredName(string $name): self
    {
        return new self(\sprintf('%s redeclared', $name));
    }

    public static function redeclaredNameInBlock(string $name): self
    {
        return new self(\sprintf('%s redeclared in this block', $name));
    }

    public static function nameMustBeFunc(string $name): self
    {
        return new self(\sprintf('cannot declare %s - must be func', $name));
    }

    public static function extraInitExpr(): self
    {
        return new self('extra init expr');
    }

    public static function iotaMisuse(): self
    {
        return new self('cannot use iota outside constant declaration');
    }

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
