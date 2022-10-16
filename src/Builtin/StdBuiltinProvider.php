<?php

declare(strict_types=1);

namespace GoPhp\Builtin;

use GoPhp\Builtin\BuiltinFunc\Append;
use GoPhp\Builtin\BuiltinFunc\Cap;
use GoPhp\Builtin\BuiltinFunc\Complex;
use GoPhp\Builtin\BuiltinFunc\Copy;
use GoPhp\Builtin\BuiltinFunc\Delete;
use GoPhp\Builtin\BuiltinFunc\Imag;
use GoPhp\Builtin\BuiltinFunc\Len;
use GoPhp\Builtin\BuiltinFunc\Make;
use GoPhp\Builtin\BuiltinFunc\New_;
use GoPhp\Builtin\BuiltinFunc\Print_;
use GoPhp\Builtin\BuiltinFunc\Println;
use GoPhp\Builtin\BuiltinFunc\Real;
use GoPhp\Env\Environment;
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
        $this->env->defineConst('true', BoolValue::true(), UntypedType::UntypedBool);
        $this->env->defineConst('false', BoolValue::false(), UntypedType::UntypedBool);
        $this->env->defineConst('iota', $this->iota, UntypedType::UntypedInt);
    }

    protected function defineVars(): void
    {
        $this->env->defineVar('nil', new UntypedNilValue(), null);
    }

    protected function defineFuncs(): void
    {
        //fixme move name to BuiltinFuncValue
        $this->env->defineBuiltinFunc(new BuiltinFuncValue(new Len('len')));
        $this->env->defineBuiltinFunc(new BuiltinFuncValue(new Cap('cap')));
        $this->env->defineBuiltinFunc(new BuiltinFuncValue(new Copy('copy')));
        $this->env->defineBuiltinFunc(new BuiltinFuncValue(new Append('append')));
        $this->env->defineBuiltinFunc(new BuiltinFuncValue(new Make('make')));
        $this->env->defineBuiltinFunc(new BuiltinFuncValue(new Delete('delete')));
        $this->env->defineBuiltinFunc(new BuiltinFuncValue(new New_('new')));
        $this->env->defineBuiltinFunc(new BuiltinFuncValue(new Complex('complex')));
        $this->env->defineBuiltinFunc(new BuiltinFuncValue(new Real('real')));
        $this->env->defineBuiltinFunc(new BuiltinFuncValue(new Imag('imag')));
        $this->env->defineBuiltinFunc(new BuiltinFuncValue(new Println('println', $this->stderr)));
        $this->env->defineBuiltinFunc(new BuiltinFuncValue(new Print_('print', $this->stderr)));
    }

    protected function defineTypes(): void
    {
        $this->env->defineType('bool', new TypeValue(NamedType::Bool));
        $this->env->defineType('string', new TypeValue(NamedType::String));
        $this->env->defineType('int', new TypeValue(NamedType::Int));
        $this->env->defineType('int8', new TypeValue(NamedType::Int8));
        $this->env->defineType('int16', new TypeValue(NamedType::Int16));
        $this->env->defineType('int32', $int32 = new TypeValue(NamedType::Int32));
        $this->env->defineType('int64', new TypeValue(NamedType::Int64));
        $this->env->defineType('uint', new TypeValue(NamedType::Uint));
        $this->env->defineType('uint8', $uint8 = new TypeValue(NamedType::Uint8));
        $this->env->defineType('uint16', new TypeValue(NamedType::Uint16));
        $this->env->defineType('uint32', new TypeValue(NamedType::Uint32));
        $this->env->defineType('uint64', new TypeValue(NamedType::Uint64));
        $this->env->defineType('uintptr', new TypeValue(NamedType::Uintptr));
        $this->env->defineType('float32', new TypeValue(NamedType::Float32));
        $this->env->defineType('float64', new TypeValue(NamedType::Float64));
        $this->env->defineType('complex64', new TypeValue(NamedType::Complex64));
        $this->env->defineType('complex128', new TypeValue(NamedType::Complex128));

        $this->env->defineTypeAlias('byte', $uint8);
        $this->env->defineTypeAlias('rune', $int32);
    }
}
