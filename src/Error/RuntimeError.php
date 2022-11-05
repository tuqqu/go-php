<?php

declare(strict_types=1);

namespace GoPhp\Error;

use GoPhp\Arg;
use GoPhp\Argv;
use GoPhp\GoType\GoType;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\BuiltinFuncValue;
use GoPhp\GoValue\Func\FuncValue;
use GoPhp\GoValue\Func\Params;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Sealable;
use GoPhp\GoValue\Sequence;
use GoPhp\GoValue\TupleValue;
use GoPhp\GoValue\UntypedNilValue;
use GoPhp\Operator;

class RuntimeError extends \RuntimeException
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
                    : $funcValue->type->params[$funcValue->type->params->len - 1]->type->name(),
                $funcValue instanceof BuiltinFuncValue
                    ? $funcValue->name()
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

    public static function valueIsNotType(GoValue $value): self
    {
        return new self(\sprintf('%s is not a type', self::valueToString($value)));
    }

    public static function undefinedOperator(Operator $op, AddressableValue $value, bool $unary = false): self
    {
        if ($op === Operator::Eq) {
            return self::cannotAssign($value);
        }

        if ($unary && $op === Operator::Mul) {
            return self::cannotIndirect($value);
        }

        return new self(
            \sprintf(
                'invalid operation: operator %s not defined on %s',
                $op->value,
                self::valueToString($value),
            )
        );
    }

    public static function cannotIndex(GoType $type): self
    {
        return new self(
            \sprintf('invalid operation: cannot index (%s)', $type->name())
        );
    }

    public static function cannotSlice(GoType $type): self
    {
        return new self(
            \sprintf('invalid operation: cannot slice (%s)', $type->name())
        );
    }

    public static function cannotAssign(GoValue $value): self
    {
        return new self(\sprintf('cannot assign to %s', self::valueToString($value)));
    }

    public static function invalidRangeValue(GoValue $value): self
    {
        return new self(\sprintf('cannot range over %s', self::valueToString($value)));
    }

    public static function lenAndCapSwapped(): self
    {
        return new self('invalid argument: length and capacity swapped');
    }

    private static function cannotIndirect(AddressableValue $value): self
    {
        return new self(
            \sprintf(
                'invalid operation: cannot indirect %s',
                self::valueToString($value),
            ),
        );
    }

    public static function cannotTakeAddressOfMapValue(GoType $type): self
    {
        return new self(
            \sprintf(
                'invalid operation: cannot take address of value (map index expression of type %s)',
                $type->name(),
            )
        );
    }

    public static function cannotTakeAddressOfValue(GoValue $value): self
    {
        return new self(
            \sprintf(
                'invalid operation: cannot take address of %s',
                self::valueToString($value),
            )
        );
    }

    public static function unsupportedOperation(string $operation, GoValue $value): self
    {
        return new self(
            \sprintf(
                'Value of type "%s" does not support "%s" operation',
                $value->type()->name(),
                $operation,
            )
        );
    }

    public static function nonFunctionCall(GoValue $value): self
    {
        return new self(
            \sprintf(
                'invalid operation: cannot call non-function %s',
                self::valueToString($value),
            )
        );
    }

    public static function expectedAssignmentOperator(Operator $op): self
    {
        return new self(
            \sprintf(
                'Unexpected operator "%s" in assignment',
                $op->value,
            )
        );
    }

    public static function wrongArgumentNumber(int|string $expected, int $actual): self
    {
        if ($expected > $actual) {
            $msg = 'not enough arguments in call (expected %s, found %d)';
        } else {
            $msg = 'too many arguments in call (expected %s, but found %d)';
        }

        return new self(\sprintf($msg, $expected, $actual));
    }

    public static function wrongArgumentType(Arg $arg, string|GoType $expectedType): self
    {
        return new self(
            \sprintf(
                'invalid argument %d (%s), expected %s',
                $arg->pos,
                $arg->value->type()->name(),
                \is_string($expectedType) ? $expectedType : $expectedType->name(),
            )
        );
    }

    public static function indexNegative(GoValue|int $value): self
    {
        return new self(
            \sprintf(
                'invalid argument: index %s must not be negative',
                \is_int($value) ? $value : self::valueToString($value),
            ),
        );
    }

    public static function cannotFullSliceString(): self
    {
        return new self('invalid operation: 3-index slice of string');
    }

    public static function notConstantExpr(GoValue $value): self
    {
        return new self(\sprintf('%s is not constant', self::valueToString($value)));
    }

    public static function indexOutOfRange(int $index, int $len): self
    {
        return new self(\sprintf('index out of range [%d] with length %d', $index, $len));
    }

    public static function invalidSliceIndices(int $low, int $high): self
    {
        return new self(\sprintf('invalid slice indices: %d < %d', $low, $high));
    }

    public static function indexOfWrongType(GoValue $index, string $type, string $where): self
    {
        return new self(
            \sprintf(
                'cannot use "%s" (%s) as %s value in %s index',
                $index->toString(),
                $index->type()->name(),
                $type,
                $where,
            )
        );
    }

    public static function uninitialisedConstant(string $name): self
    {
        return new self(\sprintf('Constant "%s" must have default value', $name));
    }

    public static function invalidFieldName(?string $field = null): self
    {
        return new self(\sprintf(
            'invalid field name%s in struct literal',
            $field === null ? '' : \sprintf(' \'%s\'', $field),
        ));
    }

    public static function undefinedFieldAccess(string $valueName, string $field, GoType $type): self
    {
        return new self(\sprintf(
            '%s undefined (type %s has no field or method %s)',
            self::fullName($valueName, $field),
            $type->name(),
            $field,
        ));
    }

    public static function constantExpectsBasicType(GoType $type): self
    {
        return new self(\sprintf('Constant must of basic type, got "%s"', $type->name()));
    }

    public static function valueIsNotConstant(GoValue $value): self
    {
        return new self(
            \sprintf(
                '%s (value of type %s) is not constant',
                $value->toString(),
                $value->type()->name(),
            ),
        );
    }

    public static function uninitilisedVarWithNoType(): self
    {
        return new self('Variables must be typed or be initialised');
    }

    public static function assignmentMismatch(int $expected, int $actual): self
    {
        return new self(\sprintf('assignment mismatch: %d variables, but %d values', $expected, $actual));
    }

    public static function labelAlreadyDefined(string $label): self
    {
        return new self(\sprintf('label %s already defined', $label));
    }

    public static function undefinedLabel(string $label): self
    {
        return new self(\sprintf('label %s not defined', $label));
    }

    public static function unfinishedArrayTypeUse(): self
    {
        return new self('invalid use of [...] array (outside a composite literal)');
    }

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
            $name = self::fullName($name, $selector);
        }

        return new self(\sprintf('%s redeclared in this block', $name));
    }

    public static function undefinedName(string $name, ?string $selector = null): self
    {
        if ($selector !== null) {
            $name = self::fullName($name, $selector);
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

    public static function jumpBeforeDecl(): self
    {
        return new self('goto jumps over declaration');
    }

    public static function missingConversionArg(GoType $type): self
    {
        return new self(\sprintf('missing argument in conversion to %s', $type->name()));
    }

    public static function tooManyConversionArgs(GoType $type): self
    {
        return new self(\sprintf('too many arguments in conversion to %s', $type->name()));
    }

    final protected static function fullName(string $name, string $selector): string
    {
        return \sprintf('%s.%s', $name, $selector);
    }

    protected static function wrongFuncArity(
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

    protected static function valueToString(GoValue $value): string
    {
        if (!$value instanceof AddressableValue) {
            return 'value';
        }

        if ($value instanceof UntypedNilValue) {
            return $value->type()->name();
        }

        if (!$value->isAddressable()) {
            if ($value instanceof Sequence) {
                return \sprintf(
                    '%s (value of type %s)',
                    $value->toString(),
                    $value->type()->name(),
                );
            }

            return \sprintf(
                '%s (%s constant)',
                $value->toString(),
                $value->type()->name(),
            );
        }

        $isConst = $value instanceof Sealable && $value->isSealed();
        $valueString = $value instanceof FuncValue ? 'value' : $value->toString();

        if ($isConst) {
            return \sprintf(
                '%s (%s constant %s)',
                $value->getName(),
                $value->type()->name(),
                $valueString,
            );
        }

        return \sprintf(
            '%s (variable of type %s)',
            $value->getName(),
            $value->type()->name(),
        );
    }

    protected static function tupleTypeToString(TupleValue $tuple): string
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
