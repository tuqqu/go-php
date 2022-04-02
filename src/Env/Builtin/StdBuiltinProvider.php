<?php

declare(strict_types=1);

namespace GoPhp\Env\Builtin;

use GoPhp\Env\Environment;
use GoPhp\Error\OperationError;
use GoPhp\GoType\BasicType;
use GoPhp\GoValue\Array\ArrayValue;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\BuiltinFuncValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Int\IntValue;
use GoPhp\GoValue\Int\UntypedIntValue;
use GoPhp\GoValue\NoValue;
use GoPhp\GoValue\Sequence;
use GoPhp\GoValue\Slice\SliceBuilder;
use GoPhp\GoValue\Slice\SliceValue;
use GoPhp\Stream\StreamProvider;
use function GoPhp\assert_arg_type;
use function GoPhp\assert_arg_value;
use function GoPhp\assert_argc;

class StdBuiltinProvider implements BuiltinProvider
{
    public function provide(): Environment
    {
        $env = new Environment();

        $env->defineConst('true', BoolValue::True, BasicType::Bool); //fixme untyped bool?
        $env->defineConst('false', BoolValue::False, BasicType::Bool); //fixme untyped bool?
        $env->defineConst('iota', new UntypedIntValue(0), BasicType::UntypedInt); //fixme add ordinal feature
        // $env->defineConst('nil', new UntypedIntValue(0), BasicType::UntypedInt); //fixme

        $env->defineBuiltinFunc('println', new BuiltinFuncValue(self::println(...)));
        $env->defineBuiltinFunc('print', new BuiltinFuncValue(self::print(...)));
        $env->defineBuiltinFunc('len', new BuiltinFuncValue(self::len(...)));
        $env->defineBuiltinFunc('cap', new BuiltinFuncValue(self::cap(...)));
        $env->defineBuiltinFunc('append', new BuiltinFuncValue(self::append(...)));

        return $env;
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
}
