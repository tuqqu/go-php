<?php

declare(strict_types=1);

namespace GoPhp\Builtin;

use GoPhp\Builtin\BuiltinFunc\Append;
use GoPhp\Builtin\BuiltinFunc\Cap;
use GoPhp\Builtin\BuiltinFunc\Delete;
use GoPhp\Builtin\BuiltinFunc\Len;
use GoPhp\Builtin\BuiltinFunc\Make;
use GoPhp\Builtin\BuiltinFunc\Print_;
use GoPhp\Builtin\BuiltinFunc\Println;
use GoPhp\Env\Environment;
use GoPhp\Env\EnvMap;
use GoPhp\GoType\NamedType;
use GoPhp\GoType\UntypedType;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\BuiltinFuncValue;
use GoPhp\GoValue\Int\BaseIntValue;
use GoPhp\GoValue\TypeValue;
use GoPhp\GoValue\UntypedNilValue;
use GoPhp\Stream\OutputStream;

class StdBuiltinProvider implements BuiltinProvider
{
    private readonly BaseIntValue&Iota $iota;
    private readonly Environment $env;
    private readonly OutputStream $stderr;

    public function __construct(OutputStream $stderr)
    {
        $this->env = new Environment();
        $this->iota = new StdIota(0);
        $this->stderr = $stderr;
    }

    public function iota(): Iota
    {
        return $this->iota;
    }

    public function env(): Environment
    {
        $this->defineConsts();
        $this->defineVars();
        $this->defineFuncs();
        $this->defineTypes();

        return $this->env;
    }

    protected function defineConsts(): void
    {
        $this->env->defineConst('true', EnvMap::NAMESPACE_TOP, BoolValue::true(), UntypedType::UntypedBool);
        $this->env->defineConst('false', EnvMap::NAMESPACE_TOP, BoolValue::false(), UntypedType::UntypedBool);
        $this->env->defineConst('iota', EnvMap::NAMESPACE_TOP, $this->iota, UntypedType::UntypedInt);
    }

    protected function defineVars(): void
    {
        $this->env->defineVar('nil', EnvMap::NAMESPACE_TOP, new UntypedNilValue(), null);
    }

    protected function defineFuncs(): void
    {
        $this->env->defineBuiltinFunc(new BuiltinFuncValue(new Len('len')));
        $this->env->defineBuiltinFunc(new BuiltinFuncValue(new Cap('cap')));
        $this->env->defineBuiltinFunc(new BuiltinFuncValue(new Append('append')));
        $this->env->defineBuiltinFunc(new BuiltinFuncValue(new Make('make')));
        $this->env->defineBuiltinFunc(new BuiltinFuncValue(new Delete('delete')));
        $this->env->defineBuiltinFunc(new BuiltinFuncValue(new Println('println', $this->stderr)));
        $this->env->defineBuiltinFunc(new BuiltinFuncValue(new Print_('print', $this->stderr)));
    }

    protected function defineTypes(): void
    {
        $this->env->defineType('bool', EnvMap::NAMESPACE_TOP, new TypeValue(NamedType::Bool));
        $this->env->defineType('string', EnvMap::NAMESPACE_TOP, new TypeValue(NamedType::String));
        $this->env->defineType('int', EnvMap::NAMESPACE_TOP, new TypeValue(NamedType::Int));
        $this->env->defineType('int8', EnvMap::NAMESPACE_TOP, new TypeValue(NamedType::Int8));
        $this->env->defineType('int16', EnvMap::NAMESPACE_TOP, new TypeValue(NamedType::Int16));
        $this->env->defineType('int32', EnvMap::NAMESPACE_TOP, $int32 = new TypeValue(NamedType::Int32));
        $this->env->defineType('int64', EnvMap::NAMESPACE_TOP, new TypeValue(NamedType::Int64));
        $this->env->defineType('uint', EnvMap::NAMESPACE_TOP, new TypeValue(NamedType::Uint));
        $this->env->defineType('uint8', EnvMap::NAMESPACE_TOP, $uint8 = new TypeValue(NamedType::Uint8));
        $this->env->defineType('uint16', EnvMap::NAMESPACE_TOP, new TypeValue(NamedType::Uint16));
        $this->env->defineType('uint32', EnvMap::NAMESPACE_TOP, new TypeValue(NamedType::Uint32));
        $this->env->defineType('uint64', EnvMap::NAMESPACE_TOP, new TypeValue(NamedType::Uint64));
        $this->env->defineType('uintptr', EnvMap::NAMESPACE_TOP, new TypeValue(NamedType::Uintptr));
        $this->env->defineType('float32', EnvMap::NAMESPACE_TOP, new TypeValue(NamedType::Float32));
        $this->env->defineType('float64', EnvMap::NAMESPACE_TOP, new TypeValue(NamedType::Float64));

        $this->env->defineTypeAlias('byte', EnvMap::NAMESPACE_TOP, $uint8);
        $this->env->defineTypeAlias('rune', EnvMap::NAMESPACE_TOP, $int32);
    }
}
