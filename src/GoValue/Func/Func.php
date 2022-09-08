<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Func;

use GoPhp\Env\Environment;
use GoPhp\Env\EnvMap;
use GoPhp\Error\InternalError;
use GoPhp\Error\ProgramError;
use GoPhp\GoType\FuncType;
use GoPhp\GoType\SliceType;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Slice\SliceBuilder;
use GoPhp\GoValue\TupleValue;
use GoPhp\GoValue\VoidValue;
use GoPhp\StmtJump\None;
use GoPhp\StmtJump\ReturnJump;
use GoPhp\StmtJump\StmtJump;

use function GoPhp\assert_arg_type;
use function GoPhp\assert_argc;
use function GoPhp\assert_types_compatible;

/**
 * @psalm-type FuncBody = \Closure(Environment, string): StmtJump
 */
final class Func
{
    /** @var FuncBody */
    public readonly \Closure $body;
    public readonly FuncType $type;
    public readonly Environment $enclosure;
    public readonly string $namespace;

    /**
     * @param FuncBody $body
     */
    public function __construct(
        \Closure $body,
        FuncType $type,
        Environment $enclosure,
        string $namespace,
    ) {
        $this->body = $body;
        $this->namespace = $namespace;
        $this->type = $type;
        $this->enclosure = new Environment(enclosing: $enclosure); // remove?
    }

    public function __invoke(GoValue ...$argv): GoValue
    {
        assert_argc(
            $argv,
            $this->type->arity,
            $this->type->variadic,
            $this->type->params,
        );

        $env = new Environment(enclosing: $this->enclosure);

        $namedReturns = [];

        if ($this->type->returns->named) {
            foreach ($this->type->returns->iter() as $param) {
                $namedReturns[] = $param->name;

                $env->defineVar(
                    $param->name,
                    EnvMap::NAMESPACE_TOP,
                    $param->type->defaultValue(),
                    $param->type,
                );
            }
        }

        foreach ($this->type->params->iter() as $i => $param) {
            if ($param->variadic) {
                $sliceType = new SliceType($param->type);
                $sliceBuilder = SliceBuilder::fromType($sliceType);

                for ($argc = \count($argv); $i < $argc; ++$i) {
                    assert_arg_type($argv[$i], $param->type, $i);

                    $sliceBuilder->pushBlindly($argv[$i]);
                }

                if ($param->name === null) {
                    continue;
                }

                $env->defineVar(
                    $param->name,
                    EnvMap::NAMESPACE_TOP,
                    $sliceBuilder->build(),
                    $sliceType,
                );

                break;
            }

            assert_arg_type($argv[$i], $param->type, $i);

            if ($param->name === null) {
                continue;
            }

            $env->defineVar(
                $param->name,
                EnvMap::NAMESPACE_TOP,
                $argv[$i],
                $param->type,
            );
        }

        /** @var StmtJump $stmtJump */
        $stmtJump = ($this->body)($env, $this->namespace);

        if ($stmtJump instanceof None) {
            return $this->type->returnArity === 0 ?
                new VoidValue() :
                throw ProgramError::wrongReturnValueNumber([], $this->type->returns);
        }

        if (!$stmtJump instanceof ReturnJump) {
            throw InternalError::unreachable($stmtJump);
        }

        if ($this->type->returnArity !== $stmtJump->len) {
            // named return: single & tuple value
            if ($stmtJump->len === 0 && !empty($namedReturns)) {
                $namedValues = [];

                foreach ($namedReturns as $namedReturn) {
                    $namedValues[] = $env->get($namedReturn, EnvMap::NAMESPACE_TOP)->unwrap();
                }

                return $this->type->returns->len === 1
                    ? $namedValues[0]
                    : new TupleValue($namedValues);
            }

            throw ProgramError::wrongReturnValueNumber($stmtJump->values(), $this->type->returns);
        }

        // void & single & tuple value return
        $values = $stmtJump->values();

        foreach ($this->type->returns->iter() as $i => $param) {
            assert_types_compatible($param->type, $values[$i]->type());
        }

        return $stmtJump->value;
    }
}
