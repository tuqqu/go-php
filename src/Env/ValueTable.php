<?php

declare(strict_types=1);

namespace GoPhp\Env;

use GoPhp\Env\EnvValue\EnvValue;
use GoPhp\Env\Error\{AlreadyDefinedError, UndefinedValueError};

final class ValueTable
{
    /** @var array<string, array<string, EnvValue>> */
    private array $values = [];

    public function tryGet(string $name, string $namespace): ?EnvValue
    {
        return $this->values[$namespace][$name]
            ?? $this->values[''][$name]
            ?? null;
    }

    public function get(string $name, string $namespace): EnvValue
    {
        return $this->tryGet($name, $namespace) ?? throw new UndefinedValueError($name);
    }

    public function add(EnvValue $envValue, ?string $namespace): void
    {
        if ($this->has($envValue->name, $namespace)) {
            throw new AlreadyDefinedError($envValue->name);
        }

        $this->values[$namespace][$envValue->name] = $envValue;
    }

    private function has(string $name, string $namespace): bool
    {
        return isset($this->values[$namespace][$name]);
    }
}
