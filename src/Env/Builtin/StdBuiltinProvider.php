<?php

declare(strict_types=1);

namespace GoPhp\Env\Builtin;

use GoPhp\Env\Environment;
use GoPhp\GoType\BasicType;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\Func\BuiltinFuncValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Int\UntypedIntValue;
use GoPhp\GoValue\NoValue;
use GoPhp\GoValue\TupleValue;
use GoPhp\Stream\StreamProvider;

final class StdBuiltinProvider implements BuiltinProvider
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

        return $env;
    }

    /**
     * @see https://pkg.go.dev/builtin#println
     */
    private static function println(StreamProvider $streams, GoValue ...$values): TupleValue|NoValue
    {
        $output = [];

        foreach ($values as $value) {
            $output[] = $value->toString();
        }

        \fwrite($streams->stderr(), \implode(' ', $output) . "\n");

        return NoValue::NoValue;
    }

    /**
     * @see https://pkg.go.dev/builtin#print
     */
    private static function print(StreamProvider $streams, GoValue ...$values): NoValue|TupleValue
    {
        $output = [];

        foreach ($values as $value) {
            $output[] = $value->toString();
        }

        \fwrite($streams->stderr(), \implode('', $output));

        return NoValue::NoValue;
    }
}
