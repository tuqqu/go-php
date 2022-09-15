<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Struct;

use GoPhp\Env\EnvValue;
use GoPhp\Env\EnvMap;
use GoPhp\Error\DefinitionError;
use GoPhp\Error\ProgramError;
use GoPhp\GoType\StructType;
use GoPhp\GoValue\GoValue;

use function GoPhp\assert_types_compatible_with_cast;

final class StructBuilder
{
    public const NAME = 'struct';

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

    public function addField(?string $name, GoValue $value): void
    {
        if ($name === null) {
            $this->orderedFields[] = $value;

            return;
        }

        $field = $this->type->fields[$name] ?? null;

        if ($field === null) {
            throw DefinitionError::invalidFieldName($name);
        }

        assert_types_compatible_with_cast($field, $value);

        $this->namedFields[$name] = $value;
    }

    public function build(): StructValue
    {
        $instanceFields = new EnvMap();

        if (!empty($this->orderedFields)) {
            if (!empty($this->namedFields)) {
                throw ProgramError::mixedStructLiteralFields();
            }

            $orderedCount = \count($this->orderedFields);
            $fieldCount = \count($this->type->fields);

            if ($orderedCount !== $fieldCount) {
                throw ProgramError::structLiteralTooManyValues($fieldCount, $orderedCount);
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
            $value = $this->namedFields[$field] ?? $type->defaultValue();
            $envValue = new EnvValue($field, $value, $type);
            $instanceFields->add($envValue);
        }

        return new StructValue($instanceFields, $this->type);
    }
}
