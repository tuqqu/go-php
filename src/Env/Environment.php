<?php

declare(strict_types=1);

namespace GoPhp\Env;

use GoPhp\Env\EnvValue\EnvValue;
use GoPhp\Env\EnvValue\ImmutableValue;
use GoPhp\Env\EnvValue\MutableValue;
use GoPhp\Env\Error\CannotBeMutatedError;
use GoPhp\Env\Error\UndefinedTypeError;
use GoPhp\Env\Error\UndefinedValueError;
use GoPhp\Error\DefinitionError;
use GoPhp\GoType\BasicType;
use GoPhp\GoType\BuiltinFuncType;
use GoPhp\GoType\GoType;
use GoPhp\GoValue\BuiltinFuncValue;
use GoPhp\GoValue\Sealable;
use GoPhp\GoValue\Func\FuncValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\TypeValue;

final class Environment
{
    private readonly ValueTable $definedValues;
    private readonly ?self $enclosing;

    public function __construct(
        ?self $enclosing = null,
    ) {
        $this->definedValues = new ValueTable();
        $this->enclosing = $enclosing;
    }

    public function defineConst(string $name, GoValue $value, BasicType $type): void
    {
        if (!$value instanceof Sealable) {
            throw DefinitionError::valueIsNotConstant($value);
        }

        $value->seal();
        $const = new ImmutableValue($name, $type, $value);
        $this->definedValues->add($const);
    }

    public function defineVar(string $name, GoValue $value, GoType $type): void
    {
        $var = new MutableValue($name, $type, $value);
        $this->definedValues->add($var);
    }

    public function defineImmutableVar(string $name, GoValue $value, GoType $type): void
    {
        $var = new ImmutableValue($name, $type, $value);
        $this->definedValues->add($var);
    }

    public function defineFunc(string $name, FuncValue $value): void
    {
        $func = new ImmutableValue($name, $value->signature->type, $value);
        $this->definedValues->add($func);
    }

    public function defineBuiltinFunc(string $name, BuiltinFuncValue $value): void
    {
        $func = new ImmutableValue($name, new BuiltinFuncType(), $value);
        $this->definedValues->add($func);
    }

    public function defineType(string $name, TypeValue $value): void
    {
        $var = new ImmutableValue($name, $value->type, $value);
        $this->definedValues->add($var);
    }

    public function defineTypeAlias(string $name, string $alias): void
    {
        $this->definedValues->alias($alias, $name);
    }

    public function get(string $name): EnvValue
    {
        return $this->definedValues->tryGet($name) ??
            $this->enclosing?->get($name) ??
            throw new UndefinedValueError($name);
    }

    public function getMut(string $name): MutableValue
    {
        $value = $this->get($name);

        if (!$value instanceof MutableValue) {
            throw new CannotBeMutatedError($name);
        }

        return $value;
    }

    public function getType(string $name): EnvValue
    {
        $envValue = $this->get($name);

        return $envValue->unwrap() instanceof TypeValue ?
            $envValue :
            throw new UndefinedTypeError($name);
    }
}
