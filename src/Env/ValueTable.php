<?php

declare(strict_types=1);

namespace GoPhp\Env;

use GoPhp\Env\EnvValue\EnvValue;
use GoPhp\Env\Error\UndefinedValueError;
use GoPhp\Error\ProgramError;

final class ValueTable
{
    /** @var array<string, array<string, EnvValue>> */
    private array $values = [];

    public function tryGet(string $name, string $namespace = '', bool $implicit = false): ?EnvValue
    {
        if (!$implicit) {
            return $this->values[$namespace][$name] ?? null;
        }

        return $this->values[$namespace][$name]
            ?? $this->values[''][$name]
            ?? null;
    }

    // fixme remove get
    public function get(string $name, string $namespace = '', bool $implicit = false): EnvValue
    {
        return $this->tryGet($name, $namespace, $implicit)
            ?? throw new UndefinedValueError($name);
    }

    public function add(EnvValue $envValue, string $namespace = ''): void
    {
        if ($this->has($envValue->name, $namespace)) {
            throw ProgramError::redeclaredNameInBlock($envValue->name);
        }

        $this->values[$namespace][$envValue->name] = $envValue;
    }

    private function has(string $name, string $namespace = ''): bool
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
    public function iter(string $namespace = ''): iterable
    {
        yield from $this->values[$namespace] ?? [];
    }
}
