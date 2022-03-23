<?php

declare(strict_types=1);

namespace GoPhp\Env;

use GoPhp\Env\EnvValue\{Constant, EnvValue, Func, Variable};
use GoPhp\Env\Error\UndefinedValueError;
use GoPhp\GoType\BasicType;
use GoPhp\GoType\ValueType;
use GoPhp\GoValue\Func\FuncValue;
use GoPhp\GoValue\GoValue;

final class Environment
{
    private readonly EnvValuesTable $definedValues;
    private readonly ?self $enclosing;

    public function __construct(
        ?self $enclosing = null,
    ) {
        $this->definedValues = new EnvValuesTable();
        $this->enclosing = $enclosing;
    }

    public function defineConst(string $name, GoValue $value, BasicType $type): void
    {
        $const = new Constant($name, $value, $type);
        $this->definedValues->add($const);
    }

    public function defineVar(string $name, GoValue $value, ValueType $type): void
    {
        $var = new Variable($name, $value, $type);
        $this->definedValues->add($var);
    }

    public function defineFunc(string $name, FuncValue $value): void
    {
        $func = new Func($name, $value, $value->signature->type);
        $this->definedValues->add($func);
    }

    public function get(string $name): EnvValue
    {
        return $this->definedValues->tryGet($name) ??
            $this->enclosing?->get($name) ??
            throw new UndefinedValueError($name);
    }

    public function assign(string $name, GoValue $value): void
    {
        $envValue = $this->definedValues->tryGet($name);
        if ($envValue !== null) {
            if (!$envValue instanceof Variable) {
                throw new \Exception('non-var assign');
            }

            $envValue->set($value);
            return;
        }

        if ($this->enclosing !== null) {
            $this->enclosing->assign($name, $value);
            return;
        }

        throw new \Exception('Assigning to undef');
    }
}
