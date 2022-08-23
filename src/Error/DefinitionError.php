<?php

declare(strict_types=1);

namespace GoPhp\Error;

use GoPhp\GoType\GoType;
use GoPhp\GoValue\GoValue;

final class DefinitionError extends \LogicException
{
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
            'invalid field name %s in struct literal',
            $field === null ?: \sprintf('\'%s\'', $field),
        ));
    }

    public static function undefinedFieldAccess(string $valueName, string $field, GoType $type): self
    {
        return new self(\sprintf(
            '%s.%s undefined (type %s has no field or method %s)',
            $valueName,
            $field,
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
        return new self(\sprintf('assignment mismatch: %d variables, but got %d values', $expected, $actual));
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
}
