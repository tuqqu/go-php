<?php

declare(strict_types=1);

namespace GoPhp\Env;

use GoPhp\Env\EnvValue\EnvValue;
use GoPhp\Env\EnvValue\ImmutableValue;
use GoPhp\Env\EnvValue\MutableValue;
use GoPhp\Env\Error\UndefinedTypeError;
use GoPhp\Env\Error\UndefinedValueError;
use GoPhp\Error\DefinitionError;
use GoPhp\GoType\BasicType;
use GoPhp\GoType\GoType;
use GoPhp\GoValue\BuiltinFuncValue;
use GoPhp\GoValue\Sealable;
use GoPhp\GoValue\Func\FuncValue;
use GoPhp\GoValue\GoValue;
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

    public function defineConst(string $name, string $namespace, GoValue $value, BasicType $type): void
    {
        if (!$value instanceof Sealable) {
            throw DefinitionError::valueIsNotConstant($value);
        }

        $value->seal();
        $value->makeNamed();

        $const = new ImmutableValue($name, $type, $value);
        $this->envMap->add($const, $namespace);
    }

    public function defineVar(string $name, string $namespace, GoValue $value, GoType $type): void
    {
        $value->makeNamed();

        $var = new MutableValue($name, $type, $value);
        $this->envMap->add($var, $namespace);
    }

    public function defineImmutableVar(string $name, string $namespace, GoValue $value, GoType $type): void
    {
        $value->makeNamed();

        $var = new ImmutableValue($name, $type, $value);
        $this->envMap->add($var, $namespace);
    }

    public function defineFunc(string $name, string $namespace, FuncValue $value): void
    {
        $value->makeNamed();

        $func = new ImmutableValue($name, $value->signature->type, $value);
        $this->envMap->add($func, $namespace);
    }

    public function defineBuiltinFunc(BuiltinFuncValue $value): void
    {
        $func = new ImmutableValue($value->name, $value->type, $value);
        $this->envMap->add($func);
    }

    public function defineType(string $name, string $namespace, TypeValue $value): void
    {
        $type = new ImmutableValue($name, $value->type, $value);
        $this->envMap->add($type, $namespace);
    }

    public function defineTypeAlias(string $alias, string $namespace, TypeValue $value): void
    {
        $this->defineType($alias, $namespace, $value);
    }

    public function get(string $name, string $namespace, bool $implicit = true): EnvValue
    {
        return $this->tryGet($name, $namespace, $implicit)
            ?? throw new UndefinedValueError($name);
    }

    public function getType(string $name, string $namespace, bool $implicit = true): EnvValue
    {
        $envValue = $this->get($name, $namespace, $implicit);

        return $envValue->unwrap() instanceof TypeValue
            ? $envValue
            : throw new UndefinedTypeError($name);
    }

    public function tryGet(string $name, string $namespace, bool $implicit = true): ?EnvValue
    {
        return $this->envMap->tryGet($name, $namespace, $implicit)
            ?? $this->enclosing?->tryGet($name, $namespace, $implicit)
            ?? null;
    }

    public function isNamespaceDefined(string $namespace): bool
    {
        return $this->envMap->hasNamespace($namespace)
            || $this->enclosing?->isNamespaceDefined($namespace);
    }
}
