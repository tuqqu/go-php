<?php

declare(strict_types=1);

namespace GoPhp\Error;

use GoParser\Ast\Keyword;
use GoParser\Ast\Stmt\Stmt;
use GoPhp\Arg;
use GoPhp\Argv;
use GoPhp\GoType\GoType;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\BuiltinFuncValue;
use GoPhp\GoValue\Func\FuncValue;
use GoPhp\GoValue\Func\Params;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Invokable;
use GoPhp\GoValue\Sealable;
use GoPhp\GoValue\Sequence;
use GoPhp\GoValue\Struct\StructValue;
use GoPhp\GoValue\TupleValue;
use GoPhp\GoValue\UntypedNilValue;
use GoPhp\GoValue\WrappedValue;
use GoPhp\JumpStatus;
use GoPhp\Operator;
use RuntimeException;

use function array_map;
use function count;
use function implode;
use function is_array;
use function is_int;
use function is_string;
use function sprintf;
use function strtolower;
use function GoPhp\construct_qualified_name;
use function GoPhp\try_unwind;

class RuntimeError extends RuntimeException implements GoError
{
    public static function numberOverflow(GoValue $value, GoType $type): self
    {
        return self::cannotUseValue($value, $type, '(overflows)');
    }

    public static function implicitConversionError(GoValue $value, GoType $type): self
    {
        return self::cannotUseValue($value, $type);
    }

    public static function expectedSliceInArgumentUnpacking(GoValue $value, Invokable $funcValue): self
    {
        [$name, $type] = match (true) {
            $funcValue instanceof FuncValue => [
                $funcValue->getName(),
                $funcValue->type->params[$funcValue->type->params->len - 1]->type->name(),
            ],
            $funcValue instanceof BuiltinFuncValue => [
                $funcValue->getName(),
                '[]T (slice)',
            ],
            default => throw InternalError::unreachable($funcValue),
        };

        return self::cannotUseArgumentAsType($value, $type, $name);
    }

    public static function cannotUseArgumentAsType(GoValue $value, GoType|string $type, ?string $func): self
    {
        return new self(
            sprintf(
                'cannot use %s as type %s in argument to %s',
                self::valueToString($value),
                is_string($type) ? $type : $type->name(),
                $func ?? 'function',
            ),
        );
    }

    public static function conversionError(GoValue $value, GoType $type): self
    {
        return new self(
            sprintf(
                'cannot convert %s to %s',
                self::valueToString($value),
                $type->name(),
            ),
        );
    }

    public static function cannotUseValue(GoValue $value, GoType $type, ?string $context = null): self
    {
        return new self(
            sprintf(
                'cannot use %s as %s value%s',
                self::valueToString($value),
                $type->name(),
                $context === null ? '' : ' ' . $context,
            ),
        );
    }

    public static function nonIntegerArrayLen(GoValue $value): self
    {
        return new self(
            sprintf(
                'array length %s must be integer',
                self::valueToString($value),
            ),
        );
    }

    public static function mismatchedTypes(GoType $a, GoType $b): self
    {
        return new self(
            sprintf(
                'invalid operation: mismatched types %s and %s',
                $a->name(),
                $b->name(),
            ),
        );
    }

    public static function untypedNilInVarDecl(): self
    {
        return new self('use of untyped nil in variable declaration');
    }

    public static function nonBooleanCondition(Stmt $context): self
    {
        /** @psalm-suppress NoInterfaceProperties */
        if (!isset($context->keyword) || !$context->keyword instanceof Keyword) {
            throw InternalError::unreachable($context);
        }

        return new self(
            sprintf(
                'non-boolean condition in %s statement',
                $context->keyword->word,
            ),
        );
    }

    public static function multipleValueInSingleContext(TupleValue $value): self
    {
        return new self(
            sprintf(
                'multiple-value (value of type %s) in single-value context',
                self::tupleTypeToString($value),
            ),
        );
    }

    public static function cannotSplatMultipleValuedReturn(int $n): self
    {
        return new self(sprintf('cannot use ... with %d-valued return value', $n));
    }

    public static function onlyComparableToNil(string $name): self
    {
        return new self(sprintf('invalid operation: %s can only be compared to nil', $name));
    }

    public static function noValueUsedAsValue(): self
    {
        return new self('(no value) used as value');
    }

    public static function builtInMustBeCalled(string $name): self
    {
        return new self(sprintf('%s (built-in) must be called', $name));
    }

    public static function valueIsNotType(GoValue $value): self
    {
        return new self(sprintf('%s is not a type', self::valueToString($value)));
    }

    public static function cannotFindPackage(string $path): self
    {
        return new self(sprintf('cannot find package "." in: %s', $path));
    }

    public static function typeOutsideTypeSwitch(): self
    {
        return new self('invalid syntax tree: use of .(type) outside type switch');
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
            sprintf(
                'invalid operation: operator %s not defined on %s',
                $op->value,
                self::valueToString($value),
            ),
        );
    }

    public static function cannotIndex(GoType $type): self
    {
        return new self(
            sprintf('invalid operation: cannot index (%s)', $type->name()),
        );
    }

    public static function cannotSlice(GoType $type): self
    {
        return new self(
            sprintf('invalid operation: cannot slice (%s)', $type->name()),
        );
    }

    public static function cannotAssign(GoValue $value): self
    {
        return new self(sprintf('cannot assign to %s', self::valueToString($value)));
    }

    public static function invalidRangeValue(GoValue $value): self
    {
        return new self(sprintf('cannot range over %s', self::valueToString($value)));
    }

    public static function lenAndCapSwapped(): self
    {
        return new self('invalid argument: length and capacity swapped');
    }

    private static function cannotIndirect(AddressableValue $value): self
    {
        return new self(
            sprintf(
                'invalid operation: cannot indirect %s',
                self::valueToString($value),
            ),
        );
    }

    public static function cannotTakeAddressOfMapValue(GoType $type): self
    {
        return new self(
            sprintf(
                'invalid operation: cannot take address of value (map index expression of type %s)',
                $type->name(),
            ),
        );
    }

    public static function cannotTakeAddressOfValue(GoValue $value): self
    {
        return new self(
            sprintf(
                'invalid operation: cannot take address of %s',
                self::valueToString($value),
            ),
        );
    }

    public static function unsupportedOperation(string $operation, GoValue $value): self
    {
        return new self(
            sprintf(
                'Value of type "%s" does not support "%s" operation',
                $value->type()->name(),
                $operation,
            ),
        );
    }

    public static function invalidNumberLiteral(string $type): self
    {
        return new self(sprintf('%s literal has no digits', $type));
    }

    public static function nonFunctionCall(GoValue $value): self
    {
        return new self(
            sprintf(
                'invalid operation: cannot call non-function %s',
                self::valueToString($value),
            ),
        );
    }

    public static function expectedAssignmentOperator(Operator $op): self
    {
        return new self(
            sprintf(
                'Unexpected operator "%s" in assignment',
                $op->value,
            ),
        );
    }

    public static function wrongArgumentNumber(int|string $expected, int $actual): self
    {
        if ($expected > $actual) {
            $msg = 'not enough arguments in call (expected %s, found %d)';
        } else {
            $msg = 'too many arguments in call (expected %s, but found %d)';
        }

        return new self(sprintf($msg, $expected, $actual));
    }

    public static function wrongArgumentType(Arg $arg, string|GoType $expectedType): self
    {
        return new self(
            sprintf(
                'invalid argument: %d (%s), expected %s',
                $arg->pos,
                self::valueToString($arg->value),
                is_string($expectedType) ? $expectedType : $expectedType->name(),
            ),
        );
    }

    public static function wrongArgumentTypeForBuiltin(GoValue $value, string $func): self
    {
        return new self(
            sprintf(
                'invalid argument: %s for built-in %s',
                self::valueToString($value),
                $func,
            ),
        );
    }

    public static function wrongArgumentTypeForMake(Arg $arg): self
    {
        return new self(
            sprintf(
                'invalid argument: cannot make %s; type must be slice, map, or channel',
                $arg->value->type()->name(),
            ),
        );
    }

    public static function nonFloatingPointArgument(GoValue $value): self
    {
        return new self(
            sprintf(
                'invalid argument: arguments have type %s, expected floating-point',
                self::valueToString($value),
            ),
        );
    }

    //todo
    public static function wrongFirstArgumentTypeForAppend(Arg $arg): self
    {
        return new self(
            sprintf(
                'first argument to append must be a slice; have %s',
                self::valueToString($arg->value),
            ),
        );
    }

    public static function nonInterfaceAssertion(GoValue $value): self
    {
        return new self(
            sprintf(
                'invalid operation: %s is not an interface',
                self::valueToString($value),
            ),
        );
    }

    public static function indexNegative(GoValue|int $value): self
    {
        return new self(
            sprintf(
                'invalid argument: index %s must not be negative',
                is_int($value) ? $value : self::valueToString($value),
            ),
        );
    }

    public static function cannotFullSliceString(): self
    {
        return new self('invalid operation: 3-index slice of string');
    }

    public static function nonConstantExpr(GoValue $value): self
    {
        return new self(sprintf('%s is not constant', self::valueToString($value)));
    }

    public static function invalidSliceIndices(int $low, int $high): self
    {
        return new self(sprintf('invalid slice indices: %d < %d', $low, $high));
    }

    public static function indexOfWrongType(GoValue $index, string $type, string $where): self
    {
        return new self(
            sprintf(
                'cannot use "%s" (%s) as %s value in %s index',
                $index->toString(),
                $index->type()->name(),
                $type,
                $where,
            ),
        );
    }

    public static function uninitialisedConstant(string $name): self
    {
        return new self(sprintf('missing init expr for %s', $name));
    }

    public static function invalidFieldName(?string $field = null): self
    {
        return new self(sprintf(
            'invalid field name%s in struct literal',
            $field === null ? '' : sprintf(' \'%s\'', $field),
        ));
    }

    public static function undefinedFieldAccess(string $valueName, string $field, GoType $type): self
    {
        return new self(sprintf(
            '%s undefined (type %s has no field or method %s)',
            construct_qualified_name($field, $valueName),
            $type->name(),
            $field,
        ));
    }

    public static function invalidConstantType(GoType $type): self
    {
        return new self(sprintf('invalid constant type %s', $type->name()));
    }

    public static function valueIsNotConstant(GoValue $value): self
    {
        return new self(
            sprintf(
                '%s (value of type %s) is not constant',
                $value->toString(),
                $value->type()->name(),
            ),
        );
    }

    public static function uninitilisedVarWithNoType(): self
    {
        return new self('expecting type');
    }

    public static function assignmentMismatch(int $expected, int $actual): self
    {
        return new self(sprintf('assignment mismatch: %d variables, but %d values', $expected, $actual));
    }

    public static function labelAlreadyDefined(string $label): self
    {
        return new self(sprintf('label %s already defined', $label));
    }

    public static function undefinedLabel(string $label): self
    {
        return new self(sprintf('label %s not defined', $label));
    }

    public static function unfinishedArrayTypeUse(): self
    {
        return new self('invalid use of [...] array (outside a composite literal)');
    }

    public static function noEntryPointFunction(string $funcName, string $packageName): self
    {
        return new self(sprintf('function %s is undeclared in the %s package', $funcName, $packageName));
    }

    public static function notEntryPointPackage(string $currentPackage, string $entryPointPackage): self
    {
        return new self(sprintf('package %s is not a %s package', $currentPackage, $entryPointPackage));
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

    public static function invalidArrayLen(): self
    {
        return new self('invalid array length');
    }

    public static function misplacedFallthrough(): self
    {
        return new self('fallthrough statement out of place');
    }

    public static function fallthroughFinalCase(): self
    {
        return new self('cannot fallthrough final case in switch');
    }

    public static function tooManyRangeVars(): self
    {
        return new self('range clause permits at most two iteration variables');
    }

    public static function redeclaredName(string $name): self
    {
        return new self(sprintf('%s redeclared', $name));
    }

    public static function duplicateMethod(string $name): self
    {
        return new self(sprintf('duplicate method %s', $name));
    }

    public static function redeclaredNameInBlock(string $name, string $selector): self
    {
        return new self(sprintf('%s redeclared in this block', construct_qualified_name($name, $selector)));
    }

    public static function undefinedName(string $name, ?string $selector = null): self
    {
        if ($selector !== null) {
            $name = construct_qualified_name($name, $selector);
        }

        return new self(sprintf('undefined: %s', $name));
    }

    public static function invalidMapKeyType(GoType $type): self
    {
        return new self(sprintf('invalid map key type %s', $type->name()));
    }

    public static function indexOutOfBounds(int $index, int $len): self
    {
        $op = $index >= $len ? '>=' : '<=';

        return new self(sprintf('index %d is out of bounds (%s %d)', $index, $op, $len));
    }

    public static function invalidReceiverType(GoType $type): self
    {
        return new self(sprintf('invalid receiver type %s', $type->name()));
    }

    public static function invalidReceiverNamedType(GoType $type): self
    {
        return new self(sprintf('invalid receiver type %s (pointer or interface type)', $type->name()));
    }

    public static function mixedStructLiteralFields(): self
    {
        return new self('mixture of field:value and value elements in struct literal');
    }

    public static function structLiteralTooManyValues(int $expected, int $actual): self
    {
        return new self(sprintf('too %s values in struct literal', $actual > $expected ? 'many' : 'few'));
    }

    public static function noNewVarsInShortAssignment(): self
    {
        return new self('no new variables on left side of :=');
    }

    public static function cannotUseBlankIdent(string $blankIdent): self
    {
        return new self(sprintf('cannot use %s as value or type', $blankIdent));
    }

    public static function nameMustBeFunc(string $name): self
    {
        return new self(sprintf('cannot declare %s - must be func', $name));
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
        return new self(sprintf('func %s must have no arguments and no return values', $funcName));
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

        return new self(sprintf($msg, $name, $expected, $actual));
    }

    public static function wrongFuncArgumentNumber(Argv $argv, Params $params): self
    {
        return self::wrongFuncArity($argv, $params, 'arguments in call');
    }

    public static function mixedReturnParams(): self
    {
        return new self('mixed named and unnamed parameters');
    }

    /**
     * @param list<GoValue> $returnValues
     */
    public static function wrongReturnValueNumber(array $returnValues, Params $params): self
    {
        return self::wrongFuncArity($returnValues, $params, 'return values');
    }

    public static function jumpBeforeDecl(): self
    {
        return new self('goto jumps over declaration');
    }

    public static function invalidLabel(string $label, JumpStatus $status): self
    {
        return new self(sprintf('invalid %s label %s', strtolower($status->name), $label));
    }

    public static function undefinedLoopLabel(string $label, JumpStatus $status): self
    {
        return new self(sprintf('%s label not defined: %s', strtolower($status->name), $label));
    }

    public static function missingConversionArg(GoType $type): self
    {
        return new self(sprintf('missing argument in conversion to %s', $type->name()));
    }

    public static function tooManyConversionArgs(GoType $type): self
    {
        return new self(sprintf('too many arguments in conversion to %s', $type->name()));
    }

    public static function methodOnNonLocalType(GoType $type): self
    {
        return new self(sprintf('cannot define new methods on non-local type %s', $type->name()));
    }

    public function getPosition(): null
    {
        return null;
    }

    final protected static function fullName(string $name, string $selector): string
    {
        return sprintf('%s.%s', $name, $selector);
    }

    /**
     * @param Argv|list<GoValue> $values
     */
    protected static function wrongFuncArity(
        Argv|array $values,
        Params $params,
        string $type,
    ): self {
        [$len, $values] = is_array($values)
            ? [count($values), $values]
            : [$values->argc, array_map(
                static fn(Arg $arg): GoValue => $arg->value,
                $values->values,
            )];

        $msg = $params->len > $len
            ? 'not enough '
            : 'too many ';

        $msg .= sprintf(
            "%s\nhave (%s)\nwant (%s)",
            $type,
            implode(', ', array_map(
                static fn(GoValue $value): string => $value->type()->name(),
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
                return sprintf(
                    '%s (value of type %s)',
                    $value->toString(),
                    $value->type()->name(),
                );
            }

            return sprintf(
                '%s (%s constant)',
                $value->toString(),
                $value->type()->name(),
            );
        }

        $isConst = $value instanceof Sealable && $value->isSealed();
        $valueString = $value instanceof FuncValue ? 'value' : $value->toString();

        if ($isConst) {
            return sprintf(
                '%s (%s constant %s)',
                $value->getName(),
                $value->type()->name(),
                $valueString,
            );
        }

        if (
            $value instanceof WrappedValue
            && ($normalizedValue = try_unwind($value)) instanceof StructValue
        ) {
            return sprintf(
                '%s%s (value of type %s)',
                $value->type()->name(),
                $normalizedValue->getFields()->empty() ? '{}' : '{…}',
                $value->type()->name(),
            );
        }

        return sprintf(
            '%s (variable of type %s)',
            $value->getName(),
            $value->type()->name(),
        );
    }

    protected static function tupleTypeToString(TupleValue $tuple): string
    {
        return sprintf(
            '(%s)',
            implode(
                ', ',
                array_map(
                    static fn(GoValue $value): string => $value->type()->name(),
                    $tuple->values,
                ),
            ),
        );
    }
}
