<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Struct;

use GoParser\Ast\Expr\Ident;
use GoParser\Ast\KeyedElement;
use GoPhp\CompositeValueBuilder;
use GoPhp\Env\EnvValue;
use GoPhp\Env\EnvMap;
use GoPhp\Error\RuntimeError;
use GoPhp\GoType\StructType;
use GoPhp\GoValue\GoValue;

use function GoPhp\assert_types_compatible_with_cast;

final class StructBuilder implements CompositeValueBuilder
{
    /** @var array<string, GoValue> */
    private array $namedFields = [];

    /** @var array<int, GoValue> */
    private array $orderedFields = [];

    private function __construct(
        private readonly StructType $type,
    ) {}

    public static function fromType(StructType $type): self
    {
        return new self($type);
    }

    public function push(KeyedElement $element, callable $evaluator): void
    {
        $name = match (true) {
            $element->key === null => null,
            $element->key instanceof Ident => $element->key->name,
            default => throw RuntimeError::invalidFieldName(),
        };

        $value = $evaluator($element->element);

        if ($name === null) {
            $this->orderedFields[] = $value;

            return;
        }

        $field = $this->type->fields[$name] ?? null;

        if ($field === null) {
            throw RuntimeError::invalidFieldName($name);
        }

        assert_types_compatible_with_cast($field, $value);

        $this->namedFields[$name] = $value;
    }

    public function build(): StructValue
    {
        $instanceFields = new EnvMap();

        if (!empty($this->orderedFields)) {
            if (!empty($this->namedFields)) {
                throw RuntimeError::mixedStructLiteralFields();
            }

            $orderedCount = \count($this->orderedFields);
            $fieldCount = \count($this->type->fields);

            if ($orderedCount !== $fieldCount) {
                throw RuntimeError::structLiteralTooManyValues($fieldCount, $orderedCount);
            }

            $i = 0;
            foreach ($this->type->fields as $field => $type) {
                $value = $this->orderedFields[$i++];
                $envValue = new EnvValue($field, $value, $type);
                $instanceFields->add($envValue);
            }

            return new StructValue($instanceFields, $this->type);
        }

        foreach ($this->type->fields as $field => $type) {
            $value = $this->namedFields[$field] ?? $type->zeroValue();
            $envValue = new EnvValue($field, $value, $type);
            $instanceFields->add($envValue);
        }

        return new StructValue($instanceFields, $this->type);
    }
}
