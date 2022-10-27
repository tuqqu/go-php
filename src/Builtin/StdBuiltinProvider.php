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
use GoPhp\GoValue\Int\IntNumber;
use GoPhp\GoValue\TypeValue;
use GoPhp\GoValue\UntypedNilValue;
use GoPhp\Stream\OutputStream;

class StdBuiltinProvider implements BuiltinProvider
{
    public function __construct(
        private readonly OutputStream $stderr,
        private readonly IntNumber&Iota $iota = new StdIota(),
    ) {}

    public function iota(): Iota
    {
        return $this->iota;
    }

    public function env(): Environment
    {
        $env = new Environment();

        $this->defineConsts($env);
        $this->defineVars($env);
        $this->defineFuncs($env);
        $this->defineTypes($env);

        return $env;
    }

    protected function defineConsts(Environment $env): void
    {
        $env->defineConst('true', BoolValue::true(), UntypedType::UntypedBool);
        $env->defineConst('false', BoolValue::false(), UntypedType::UntypedBool);
        $env->defineConst('iota', $this->iota, UntypedType::UntypedInt);
    }

    protected function defineVars(Environment $env): void
    {
        $env->defineVar('nil', new UntypedNilValue(), null);
    }

    protected function defineFuncs(Environment $env): void
    {
        $env->defineBuiltinFunc(new BuiltinFuncValue(new Len('len')));
        $env->defineBuiltinFunc(new BuiltinFuncValue(new Cap('cap')));
        $env->defineBuiltinFunc(new BuiltinFuncValue(new Copy('copy')));
        $env->defineBuiltinFunc(new BuiltinFuncValue(new Append('append')));
        $env->defineBuiltinFunc(new BuiltinFuncValue(new Make('make')));
        $env->defineBuiltinFunc(new BuiltinFuncValue(new Delete('delete')));
        $env->defineBuiltinFunc(new BuiltinFuncValue(new New_('new')));
        $env->defineBuiltinFunc(new BuiltinFuncValue(new Complex('complex')));
        $env->defineBuiltinFunc(new BuiltinFuncValue(new Real('real')));
        $env->defineBuiltinFunc(new BuiltinFuncValue(new Imag('imag')));
        $env->defineBuiltinFunc(new BuiltinFuncValue(new Println('println', $this->stderr)));
        $env->defineBuiltinFunc(new BuiltinFuncValue(new Print_('print', $this->stderr)));
    }

    protected function defineTypes(Environment $env): void
    {
        $env->defineType('bool', new TypeValue(NamedType::Bool));
        $env->defineType('string', new TypeValue(NamedType::String));
        $env->defineType('int', new TypeValue(NamedType::Int));
        $env->defineType('int8', new TypeValue(NamedType::Int8));
        $env->defineType('int16', new TypeValue(NamedType::Int16));
        $env->defineType('int32', $int32 = new TypeValue(NamedType::Int32));
        $env->defineType('int64', new TypeValue(NamedType::Int64));
        $env->defineType('uint', new TypeValue(NamedType::Uint));
        $env->defineType('uint8', $uint8 = new TypeValue(NamedType::Uint8));
        $env->defineType('uint16', new TypeValue(NamedType::Uint16));
        $env->defineType('uint32', new TypeValue(NamedType::Uint32));
        $env->defineType('uint64', new TypeValue(NamedType::Uint64));
        $env->defineType('uintptr', new TypeValue(NamedType::Uintptr));
        $env->defineType('float32', new TypeValue(NamedType::Float32));
        $env->defineType('float64', new TypeValue(NamedType::Float64));
        $env->defineType('complex64', new TypeValue(NamedType::Complex64));
        $env->defineType('complex128', new TypeValue(NamedType::Complex128));

        $env->defineTypeAlias('byte', $uint8);
        $env->defineTypeAlias('rune', $int32);
    }
}
