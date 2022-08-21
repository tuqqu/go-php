<?php

declare(strict_types=1);

namespace GoPhp\Env;

use GoPhp\Error\DefinitionError;
use GoPhp\Error\ProgramError;
use GoPhp\GoType\BasicType;
use GoPhp\GoType\GoType;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\BuiltinFuncValue;
use GoPhp\GoValue\Func\FuncValue;
use GoPhp\GoValue\Sealable;
use GoPhp\GoValue\TypeValue;

final class Environment
{
    private readonly EnvMap $envMap;
    private readonly ?self $enclosing;

    public function __construct(?self $enclosing = null)
    {
        $this->envMap = new EnvMap();
        $this->enclosing = $enclosing;
    }

    public function defineConst(string $name, string $namespace, AddressableValue $value, BasicType $type): void
    {
        if (!$value instanceof Sealable) {
            throw DefinitionError::valueIsNotConstant($value);
        }

        $value->seal();

        $this->defineAddressableValue($name, $namespace, $value, $type);
    }

    public function defineVar(string $name, string $namespace, AddressableValue $value, GoType $type): void
    {
        $this->defineAddressableValue($name, $namespace, $value, $type);
    }

    public function defineImmutableVar(string $name, string $namespace, AddressableValue $value, GoType $type): void
    {
        $this->defineAddressableValue($name, $namespace, $value, $type);
    }

    public function defineFunc(string $name, string $namespace, FuncValue $value): void
    {
        $this->defineAddressableValue($name, $namespace, $value, $value->signature->type);
    }

    public function defineBuiltinFunc(BuiltinFuncValue $value): void
    {
        $func = new EnvValue($value->name, $value->type, $value);
        $this->envMap->add($func);
    }

    public function defineType(string $name, string $namespace, TypeValue $value): void
    {
        $type = new EnvValue($name, $value->type, $value);
        $this->envMap->add($type, $namespace);
    }

    public function defineTypeAlias(string $alias, string $namespace, TypeValue $value): void
    {
        $this->defineType($alias, $namespace, $value);
    }

    public function get(string $name, string $namespace, bool $implicit = true): EnvValue
    {
        return $this->tryGet($name, $namespace, $implicit)
            ?? throw ProgramError::undefinedName($name);
    }

    public function getType(string $name, string $namespace, bool $implicit = true): EnvValue
    {
        $envValue = $this->get($name, $namespace, $implicit);

        return $envValue->unwrap() instanceof TypeValue
            ? $envValue
            :  throw ProgramError::undefinedName($name);
    }

    public function isNamespaceDefined(string $namespace): bool
    {
        return $this->envMap->hasNamespace($namespace)
            || $this->enclosing?->isNamespaceDefined($namespace);
    }

    private function tryGet(string $name, string $namespace, bool $implicit = true): ?EnvValue
    {
        return $this->envMap->tryGet($name, $namespace, $implicit)
            ?? $this->enclosing?->tryGet($name, $namespace, $implicit)
            ?? null;
    }

    private function defineAddressableValue(string $name, string $namespace, AddressableValue $value, GoType $type): void {
        $value->makeAddressable();

        $this->envMap->add(
            new EnvValue($name, $type, $value),
            $namespace,
        );
    }
}
