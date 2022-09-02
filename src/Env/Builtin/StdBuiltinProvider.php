<?php

declare(strict_types=1);

namespace GoPhp\Env\Builtin;

use GoPhp\Env\Environment;
use GoPhp\Env\EnvMap;
use GoPhp\Error\OperationError;
use GoPhp\GoType\GoType;
use GoPhp\GoType\MapType;
use GoPhp\GoType\NamedType;
use GoPhp\GoType\SliceType;
use GoPhp\GoType\UntypedNilType;
use GoPhp\GoType\UntypedType;
use GoPhp\GoValue\Array\ArrayValue;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\BuiltinFuncValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Int\BaseIntValue;
use GoPhp\GoValue\Int\IntValue;
use GoPhp\GoValue\Int\Iota;
use GoPhp\GoValue\Map\MapBuilder;
use GoPhp\GoValue\Map\MapValue;
use GoPhp\GoValue\NilValue;
use GoPhp\GoValue\Sequence;
use GoPhp\GoValue\Slice\SliceBuilder;
use GoPhp\GoValue\Slice\SliceValue;
use GoPhp\GoValue\TypeValue;
use GoPhp\GoValue\VoidValue;
use GoPhp\Stream\OutputStream;

use function GoPhp\assert_arg_int;
use function GoPhp\assert_arg_value;
use function GoPhp\assert_argc;
use function GoPhp\assert_index_positive;

class StdBuiltinProvider implements BuiltinProvider
{
    private readonly BaseIntValue&Iota $iota;
    private readonly Environment $env;
    private readonly OutputStream $stderr;

    public function __construct(OutputStream $stderr)
    {
        $this->env = new Environment();
        $this->iota = static::createIota();
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
        $this->env->defineImmutableVar('nil', EnvMap::NAMESPACE_TOP, new NilValue($type = new UntypedNilType()), $type);
    }

    protected function defineFuncs(): void
    {
        $this->env->defineBuiltinFunc(new BuiltinFuncValue('println', $this->println(...)));
        $this->env->defineBuiltinFunc(new BuiltinFuncValue('print', $this->print(...)));
        $this->env->defineBuiltinFunc(new BuiltinFuncValue('len', self::len(...)));
        $this->env->defineBuiltinFunc(new BuiltinFuncValue('cap', self::cap(...)));
        $this->env->defineBuiltinFunc(new BuiltinFuncValue('append', self::append(...)));
        $this->env->defineBuiltinFunc(new BuiltinFuncValue('make', self::make(...)));
        $this->env->defineBuiltinFunc(new BuiltinFuncValue('delete', self::delete(...)));
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

    /**
     * @see https://pkg.go.dev/builtin#println
     */
    protected function println(GoValue ...$values): VoidValue
    {
        $output = [];

        foreach ($values as $value) {
            $output[] = $value->toString();
        }

        $this->stderr->writeln(\implode(' ', $output));

        return new VoidValue();
    }

    /**
     * @see https://pkg.go.dev/builtin#print
     */
    protected function print(GoValue ...$values): VoidValue
    {
        $output = [];

        foreach ($values as $value) {
            $output[] = $value->toString();
        }

        $this->stderr->write(\implode('', $output));

        return new VoidValue();
    }

    /**
     * @see https://pkg.go.dev/builtin#len
     */
    protected static function len(GoValue ...$values): IntValue
    {
        assert_argc($values, 1);
        assert_arg_value($values[0], Sequence::class, 'slice, array, string, map', 1);

        return new IntValue($values[0]->len());
    }

    /**
     * @see https://pkg.go.dev/builtin#cap
     */
    protected static function cap(GoValue ...$values): IntValue
    {
        assert_argc($values, 1);

        $value = $values[0];

        if ($value instanceof ArrayValue) {
            return new IntValue($value->len());
        }

        if ($value instanceof SliceValue) {
            return new IntValue($value->cap());
        }

        throw OperationError::wrongArgumentType($value->type(), 'slice, array', 1);
    }

    /**
     * @see https://pkg.go.dev/builtin#delete
     */
    protected function delete(GoValue ...$values): VoidValue
    {
        assert_argc($values, 2);
        assert_arg_value($values[0], MapValue::class, MapValue::NAME, 1);

        $values[0]->delete($values[1]);

        return new VoidValue();
    }

    /**
     * @see https://pkg.go.dev/builtin#append
     */
    protected static function append(GoValue ...$values): SliceValue
    {
        assert_argc($values, 1, variadic: true);
        assert_arg_value($values[0], SliceValue::class, SliceValue::NAME, 1);

        /** @var SliceValue $slice */
        $slice = $values[0]->clone();

        unset($values[0]);
        foreach ($values as $value) {
            $slice->append($value);
        }

        return $slice;
    }

    /**
     * @see https://pkg.go.dev/builtin#make
     */
    protected static function make(GoValue ...$values): SliceValue|MapValue
    {
        assert_argc($values, 1, variadic: true);
        assert_arg_value($values[0], TypeValue::class, 'type', 1);

        /** @var TypeValue $type */
        $type = $values[0];
        $argc = \count($values);

        if ($type->type instanceof SliceType) {
            if ($argc > 3) {
                throw OperationError::wrongArgumentNumber('2 or 3', $argc);
            }

            $builder = SliceBuilder::fromType($type->type);

            if (isset($values[1])) {
                assert_arg_int($values[1], 2);

                $len = $values[1]->unwrap();

                assert_index_positive($len);

                for ($i = 0; $i < $len; ++$i) {
                    $builder->pushBlindly($type->type->elemType->defaultValue());
                }
            }

            if (isset($values[2])) {
                assert_arg_int($values[2], 3);

                $cap = $values[2]->unwrap();

                assert_index_positive($cap);

                if ($cap < ($len ?? 0)) {
                    throw OperationError::lenAndCapSwapped();
                }

                $builder->setCap($cap);
            }

            return $builder->build();
        }

        if ($type->type instanceof MapType) {
            if ($argc > 2) {
                throw OperationError::wrongArgumentNumber(2, $argc);
            }

            if (isset($values[1])) {
                // we do not use this value, just validating it
                assert_arg_value($values[1], BaseIntValue::class, 'int', 3);
                assert_index_positive($values[1]->unwrap());
            }

            return MapBuilder::fromType($type->type)->build();
        }

        throw OperationError::wrongArgumentType($type->type, 'slice, map or channel', 1);
    }

    protected static function createIota(): BaseIntValue&Iota
    {
        return new class (0) extends BaseIntValue implements Iota {
            public function type(): GoType
            {
                return UntypedType::UntypedInt;
            }

            public function setValue(int $value): void
            {
                $this->value = $value;
            }
        };
    }
}
