<?php

declare(strict_types=1);

namespace GoPhp\Env;

use GoPhp\Env\EnvValue\EnvValue;
use GoPhp\Env\Error\{AlreadyDefinedError, UndefinedValueError};

final class ValueTable
{
    /** @var array<string, EnvValue> */
    private array $values = [];

    public function has(string $name): bool
    {
        return isset($this->values[$name]);
    }

    public function tryGet(string $name): ?EnvValue
    {
        return $this->values[$name] ?? null;
    }

    public function get(string $name): EnvValue
    {
        return $this->tryGet($name) ?? throw new UndefinedValueError($name);
    }

    public function add(EnvValue $envValue): void
    {
        if ($this->has($envValue->name)) {
            throw new AlreadyDefinedError($envValue->name);
        }

        $this->values[$envValue->name] = $envValue;
    }

    public function alias(string $alias, string $name): void
    {
        $this->values[$alias] = $this->get($name);
    }
}
