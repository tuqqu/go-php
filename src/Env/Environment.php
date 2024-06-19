<?php

declare(strict_types=1);

namespace GoPhp\Env;

use GoPhp\Error\RuntimeError;
use GoPhp\GoType\GoType;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\BlankValue;
use GoPhp\GoValue\BuiltinFuncValue;
use GoPhp\GoValue\Func\FuncValue;
use GoPhp\GoValue\Hashable;
use GoPhp\GoValue\Sealable;
use GoPhp\GoValue\TypeValue;

final class Environment
{
    public const string BLANK_IDENT = '_';

    public readonly string $blankIdent;
    private readonly EnvMap $envMap;
    private readonly MethodSet $registeredMethods;
    private readonly ?self $enclosing;

    private function __construct(?self $enclosing, string $blankIdent)
    {
        $this->registeredMethods = new MethodSet();
        $this->enclosing = $enclosing;
        $this->blankIdent = $blankIdent;
        $this->envMap = new EnvMap();
        $this->envMap->add(new EnvValue($this->blankIdent, new BlankValue()));
    }

    public static function new(string $blankIdent = self::BLANK_IDENT): self
    {
        return new self(null, $blankIdent);
    }

    public static function fromEnclosing(self $enclosing): self
    {
        return new self($enclosing, $enclosing->blankIdent);
    }

    public function defineConst(string $name, AddressableValue $value, GoType $type, string $namespace = EnvMap::NAMESPACE_TOP): void
    {
        if (!$value instanceof Sealable) {
            throw RuntimeError::valueIsNotConstant($value);
        }

        $value->seal();
        $this->defineAddressableValue($name, $value, $type, $namespace);
    }

    public function defineVar(string $name, AddressableValue $value, ?GoType $type, string $namespace = EnvMap::NAMESPACE_TOP): void
    {
        $this->defineAddressableValue($name, $value, $type, $namespace);
    }

    public function defineFunc(string $name, FuncValue $value, string $namespace = EnvMap::NAMESPACE_TOP): void
    {
        $value->seal();
        $this->defineAddressableValue($name, $value, $value->type, $namespace);
    }

    public function registerMethod(string $name, FuncValue $value, GoType $receiver): void
    {
        $this->registeredMethods->add($receiver, $name, $value);
    }

    public function getMethod(string $name, GoType $receiver): ?FuncValue
    {
        if (!$receiver instanceof Hashable) {
            return null;
        }

        return $this->registeredMethods->tryGet($receiver, $name)
            ?? $this->enclosing?->getMethod($name, $receiver);
    }

    public function hasMethod(string $name, GoType $receiver): bool
    {
        if (!$receiver instanceof Hashable) {
            return false;
        }

        return $this->registeredMethods->has($receiver, $name)
            || $this->enclosing?->hasMethod($name, $receiver);
    }

    public function defineBuiltinFunc(BuiltinFuncValue $value): void
    {
        $func = new EnvValue($value->getName(), $value);
        $this->envMap->add($func);
    }

    public function defineType(string $name, TypeValue $value, string $namespace = EnvMap::NAMESPACE_TOP): void
    {
        $this->defineAddressableValue($name, $value, $value->type, $namespace);
    }

    public function defineTypeAlias(string $alias, TypeValue $value, string $namespace = EnvMap::NAMESPACE_TOP): void
    {
        $this->defineType($alias, $value, $namespace);
    }

    public function get(string $name, string $namespace, bool $implicit = true): EnvValue
    {
        return $this->tryGet($name, $namespace, $implicit)
            ?? throw RuntimeError::undefinedName($name);
    }

    public function isNamespaceDefined(string $namespace): bool
    {
        return $this->envMap->hasNamespace($namespace)
            || $this->enclosing?->isNamespaceDefined($namespace);
    }

    public function tryGetFromSameScope(string $name): ?EnvValue
    {
        return $this->envMap->tryGet($name);
    }

    private function tryGet(string $name, string $namespace, bool $implicit = true): ?EnvValue
    {
        return $this->envMap->tryGet($name, $namespace, $implicit)
            ?? $this->enclosing?->tryGet($name, $namespace, $implicit)
            ?? null;
    }

    private function defineAddressableValue(
        string $name,
        AddressableValue $value,
        ?GoType $type,
        string $namespace,
    ): void {
        $value->makeAddressable();
        $envValue = new EnvValue($name, $value, $type, $namespace);

        if ($name === $this->blankIdent) {
            return;
        }

        $this->envMap->add($envValue, $namespace);
    }
}
