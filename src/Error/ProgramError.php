<?php

declare(strict_types=1);

namespace GoPhp\Error;

use GoPhp\Argv;
use GoPhp\GoType\GoType;
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

    public static function redeclaredNameInBlock(string $name, ?string $selector = null): self
    {
        if ($selector !== null) {
            $name = DefinitionError::fullName($name, $selector);
        }

        return new self(\sprintf('%s redeclared in this block', $name));
    }

    public static function undefinedName(string $name, ?string $selector = null): self
    {
        if ($selector !== null) {
            $name = DefinitionError::fullName($name, $selector);
        }

        return new self(\sprintf('undefined: %s', $name));
    }

    public static function invalidReceiverType(GoType $type): self
    {
        return new self(\sprintf('invalid receiver type %s', $type->name()));
    }

    public static function mixedStructLiteralFields(): self
    {
        return new self('mixture of field:value and value elements in struct literal');
    }

    public static function structLiteralTooManyValues(int $expected, int $actual): self
    {
        return new self(\sprintf('too %s values in struct literal', $actual > $expected ? 'many' : 'few'));
    }

    public static function noNewVarsInShortAssignment(): self
    {
        return new self('no new variables on left side of :=');
    }

    public static function cannotUseBlankIdent(string $blankIdent): self
    {
        return new self(\sprintf('cannot use %s as value or type', $blankIdent));
    }

    public static function nameMustBeFunc(string $name): self
    {
        return new self(\sprintf('cannot declare %s - must be func', $name));
    }

    public static function multipleReceivers(): self
    {
        return new self('method has multiple receivers');
    }

    public static function extraInitExpr(): self
    {
        return new self('extra init expr');
    }

    public static function funcMustBeNoArgsVoid(string $funcName): self
    {
        return new self(\sprintf('func %s must have no arguments and no return values', $funcName));
    }

    public static function iotaMisuse(): self
    {
        return new self('cannot use iota outside constant declaration');
    }

    public static function wrongBuiltinArgumentNumber(int|string $expected, int $actual, string $name): self
    {
        $msg = $expected > $actual
            ? 'invalid operation: not enough arguments for %s() (expected %s, found %d)'
            : 'invalid operation: too many arguments for %s() (expected %s, found %d)';

        return new self(\sprintf($msg, $expected, $actual, $name));
    }

    public static function wrongFuncArgumentNumber(Argv $argv, Params $params): self
    {
        return self::wrongFuncArity($argv, $params, 'arguments in call');
    }

    public static function wrongReturnValueNumber(array $returnValues, Params $params): self
    {
        return self::wrongFuncArity($returnValues, $params, 'return values');
    }

    private static function wrongFuncArity(
        Argv|array $values,
        Params $params,
        string $type,
    ): self {
        [$len, $values] = \is_array($values)
            ? [\count($values), $values]
            : [$values->argc, $values->values];

        $msg = $params->len > $len ?
            'not enough ' :
            'too many ';

        $msg .= \sprintf(
            "%s\nhave (%s)\nwant (%s)",
            $type,
            \implode(', ', \array_map(
                static fn (GoValue $value): string => $value->type()->name(),
                $values,
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
