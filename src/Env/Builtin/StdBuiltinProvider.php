<?php

declare(strict_types=1);

namespace GoPhp\Env\Builtin;

use GoPhp\Env\Environment;
use GoPhp\Error\OperationError;
use GoPhp\GoType\NamedType;
use GoPhp\GoType\GoType;
use GoPhp\GoType\MapType;
use GoPhp\GoType\SliceType;
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
use GoPhp\GoValue\NoValue;
use GoPhp\GoValue\Sequence;
use GoPhp\GoValue\Slice\SliceBuilder;
use GoPhp\GoValue\Slice\SliceValue;
use GoPhp\GoValue\TypeValue;
use GoPhp\Stream\StreamProvider;
use function GoPhp\assert_arg_type;
use function GoPhp\assert_arg_value;
use function GoPhp\assert_argc;

class StdBuiltinProvider implements BuiltinProvider
{
    private Iota $iota;
    private Environment $env;

    public function __construct()
    {
        $this->iota = new class (0) extends BaseIntValue implements Iota {
            public function type(): GoType
            {
                return UntypedType::UntypedInt;
            }

            public function setValue(int $value): void
            {
                $this->value = $value;
            }
        };
        $this->env = new Environment();
        $this->buildStdEnv();
    }

    public function iota(): Iota
    {
        return $this->iota;
    }

    public function env(): Environment
    {
        return $this->env;
    }

    protected function buildStdEnv(): void
    {
        $this->defineStdConsts();
        $this->defineFuncs();
    }

    protected function defineStdConsts(): void
    {
        $this->env->defineConst('true', BoolValue::True, NamedType::Bool); //fixme untyped bool?
        $this->env->defineConst('false', BoolValue::False, NamedType::Bool); //fixme untyped bool?
        $this->env->defineConst('iota', $this->iota, UntypedType::UntypedInt);
        // $this->env->defineConst('nil', new UntypedIntValue(0), BasicType::UntypedInt); //fixme
    }

    protected function defineFuncs(): void
    {
        $this->env->defineBuiltinFunc('println', new BuiltinFuncValue(self::println(...)));
        $this->env->defineBuiltinFunc('print', new BuiltinFuncValue(self::print(...)));
        $this->env->defineBuiltinFunc('len', new BuiltinFuncValue(self::len(...)));
        $this->env->defineBuiltinFunc('cap', new BuiltinFuncValue(self::cap(...)));
        $this->env->defineBuiltinFunc('append', new BuiltinFuncValue(self::append(...)));
        $this->env->defineBuiltinFunc('make', new BuiltinFuncValue(self::make(...)));
    }


    /**
     * @see https://pkg.go.dev/builtin#println
     */
    protected static function println(StreamProvider $streams, GoValue ...$values): NoValue
    {
        $output = [];

        foreach ($values as $value) {
            $output[] = $value->toString();
        }

        $streams->stderr()->writeln(\implode(' ', $output));

        return NoValue::NoValue;
    }

    /**
     * @see https://pkg.go.dev/builtin#print
     */
    protected static function print(StreamProvider $streams, GoValue ...$values): NoValue
    {
        $output = [];

        foreach ($values as $value) {
            $output[] = $value->toString();
        }

        $streams->stderr()->write(\implode('', $output));

        return NoValue::NoValue;
    }

    /**
     * @see https://pkg.go.dev/builtin#len
     */
    protected static function len(StreamProvider $streams, GoValue ...$values): IntValue
    {
        assert_argc($values, 1);
        assert_arg_value($values[0], Sequence::class, 'slice, array, string', 1);

        return new IntValue($values[0]->len());
    }

    /**
     * @see https://pkg.go.dev/builtin#cap
     */
    protected static function cap(StreamProvider $streams, GoValue ...$values): IntValue
    {
        assert_argc($values, 1);

        $value = $values[0];

        if ($value instanceof ArrayValue) {
            return new IntValue($value->len());
        }

        if ($value instanceof SliceValue) {
            return new IntValue($value->len()); //fixme
        }

        throw OperationError::wrongArgumentType($value->type(), 'slice, array', 1);
    }

    /**
     * @see https://pkg.go.dev/builtin#append
     */
    protected static function append(StreamProvider $streams, GoValue ...$values): SliceValue
    {
        assert_argc($values, 1, variadic: true);
        assert_arg_value($values[0], SliceValue::class, SliceValue::NAME, 1);

        /** @var SliceValue $slice */
        $slice = $values[0];
        $sliceBuilder = SliceBuilder::fromValue($slice);

        unset($values[0]);
        foreach ($values as $i => $value) {
            assert_arg_type($value, $slice->type(), $i + 1);
            $sliceBuilder->pushBlindly($value);
        }

        return $sliceBuilder->build();
    }

    /**
     * @see https://pkg.go.dev/builtin#make
     */
    protected static function make(StreamProvider $streams, GoValue ...$values): SliceValue|MapValue
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

            // fixme float .0 truncation allowed
            if (isset($values[1])) {
                assert_arg_value($values[1], BaseIntValue::class, 'int', 2);
                $len = $values[1]->unwrap();
            } else {
                $len = 0;
            }

            if (isset($values[2])) {
                // we do not use this value, just validating it
                assert_arg_value($values[2], BaseIntValue::class, 'int', 3);
            }

            $builder = SliceBuilder::fromType($type->type);
            for ($i = 0; $i < $len; ++$i) {
                $builder->pushBlindly($type->type->internalType->defaultValue());
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
            }

            return MapBuilder::fromType($type->type)->build();
        }

        throw OperationError::wrongArgumentType($type->type, 'slice, map or channel', 1);
    }
}
