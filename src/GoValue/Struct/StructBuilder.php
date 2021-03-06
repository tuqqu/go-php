<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Struct;

use GoPhp\Env\EnvValue\MutableValue;
use GoPhp\Env\ValueTable;
use GoPhp\Error\DefinitionError;
use GoPhp\GoType\StructType;
use GoPhp\GoValue\GoValue;

use function GoPhp\assert_types_compatible_with_cast;

final class StructBuilder
{
    public const NAME = 'struct';

    private array $initFields = [];

    private function __construct(
        private readonly StructType $type,
    ) {}

    public static function fromType(StructType $type): self
    {
        return new self($type);
    }

    public function addField(string $name, GoValue $value): void
    {
        $field = $this->type->fields[$name] ?? null;

        if ($field === null) {
            throw DefinitionError::invalidFieldName($name);
        }

        assert_types_compatible_with_cast($field, $value);

        $this->initFields[$name] = $value;
    }

    public function build(): StructValue
    {
        $instanceFields = new ValueTable();

        foreach ($this->type->fields as $field => $type) {
            $value = $this->initFields[$field] ?? $type->defaultValue();
            $value->makeNamed();

            $envValue = new MutableValue($field, $type, $value);

            $instanceFields->add($envValue);
        }

        return new StructValue($instanceFields, $this->type);
    }
}
