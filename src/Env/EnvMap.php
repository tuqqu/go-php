<?php

declare(strict_types=1);

namespace GoPhp\Env;

use GoPhp\Error\ProgramError;

final class EnvMap
{
    public const NAMESPACE_TOP = '';

    /** @var array<string, array<string, EnvValue>> */
    private array $values = [];

    public function tryGet(string $name, string $namespace = self::NAMESPACE_TOP, bool $implicit = false): ?EnvValue
    {
        if (!$implicit) {
            return $this->values[$namespace][$name] ?? null;
        }

        return $this->values[$namespace][$name]
            ?? $this->values[self::NAMESPACE_TOP][$name] //fixme refactor double lookup for NAMESPACE_TOP
            ?? null;
    }

    public function get(string $name, string $namespace = self::NAMESPACE_TOP, bool $implicit = false): EnvValue
    {
        return $this->tryGet($name, $namespace, $implicit)
            ?? throw ProgramError::undefinedName($name);
    }

    public function add(EnvValue $envValue, string $namespace = self::NAMESPACE_TOP): void
    {
        if ($this->has($envValue->name, $namespace)) {
            throw ProgramError::redeclaredNameInBlock($envValue->name);
        }

        $this->values[$namespace][$envValue->name] = $envValue;
    }

    private function has(string $name, string $namespace = self::NAMESPACE_TOP): bool
    {
        return isset($this->values[$namespace][$name]);
    }

    public function hasNamespace(string $namespace): bool
    {
        return isset($this->values[$namespace]);
    }

    /**
     * @return iterable<string, EnvValue>
     */
    public function iter(string $namespace = self::NAMESPACE_TOP): iterable
    {
        yield from $this->values[$namespace] ?? [];
    }

    public function copy(): self
    {
        $copy = new self();

        foreach ($this->iter() as $field) {
            $copy->add($field->copy());
        }

        return $copy;
    }
}
