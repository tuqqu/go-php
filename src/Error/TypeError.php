<?php

declare(strict_types=1);

namespace GoPhp\Error;

use GoPhp\GoType\GoType;
use GoPhp\GoValue\BuiltinFuncValue;
use GoPhp\GoValue\Func\FuncValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\TupleValue;

final class TypeError extends OperationError
{
    public static function implicitConversionError(GoValue $value, GoType $type): self
    {
        return new self(
            \sprintf(
                'cannot use %s as %s value',
                self::valueToString($value),
                $type->name(),
            )
        );
    }

    public static function expectedSliceInArgumentUnpacking(GoValue $value, FuncValue|BuiltinFuncValue $funcValue): self
    {
        return new self(
            \sprintf(
                'cannot use %s as type %s in argument to %s',
                self::valueToString($value),
                $funcValue instanceof BuiltinFuncValue
                    ? '[]T (slice)'
                    : $funcValue->type->params->params[$funcValue->type->params->len - 1]->type->name(),
                $funcValue instanceof BuiltinFuncValue
                    ? $funcValue->name
                    : $funcValue->getName(),
            ),
        );
    }

    public static function conversionError(GoValue $value, GoType $type): self
    {
        return new self(
            \sprintf(
                'cannot convert %s to %s',
                self::valueToString($value),
                $type->name(),
            ),
        );
    }

    public static function invalidArrayLen(GoValue $value): self
    {
        return new self(
            \sprintf(
                'array length %s must be integer',
                self::valueToString($value),
            ),
        );
    }

    public static function mismatchedTypes(GoType $a, GoType $b): self
    {
        return new self(
            \sprintf(
                'invalid operation: mismatched types %s and %s',
                $a->name(),
                $b->name(),
            )
        );
    }

    public static function untypedNilInVarDecl(): self
    {
        return new self('use of untyped nil in variable declaration');
    }

    public static function valueOfWrongType(GoValue $value, GoType|string $expected): self
    {
        return new self(
            \sprintf(
                'Got value of type "%s", whilst expecting "%s"',
                $value->type()->name(),
                \is_string($expected) ? $expected : $expected->name(),
            )
        );
    }

    public static function multipleValueInSingleContext(TupleValue $value): self
    {
        return new self(
            \sprintf(
                'multiple-value (value of type %s) in single-value context',
                self::tupleTypeToString($value),
            ),
        );
    }

    public static function cannotSplatMultipleValuedReturn(int $n): self
    {
        return new self(\sprintf('cannot use ... with %d-valued return value', $n));
    }

    public static function onlyComparableToNil(string $name): self
    {
        return new self(\sprintf('invalid operation: %s can only be compared to nil', $name));
    }

    public static function noValueUsedAsValue(): self
    {
        return new self('(no value) used as value');
    }

    public static function builtInMustBeCalled(string $name): self
    {
        return new self(\sprintf('%s (built-in) must be called', $name));
    }

    private static function tupleTypeToString(TupleValue $tuple): string
    {
        return \sprintf(
            '(%s)',
            \implode(
                ', ',
                \array_map(
                    static fn (GoValue $value): string => $value->type()->name(),
                    $tuple->values,
                ),
            ),
        );
    }
}
