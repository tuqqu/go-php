<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Func;

use GoPhp\Argv;
use GoPhp\Env\Environment;
use GoPhp\Error\InternalError;
use GoPhp\Error\ProgramError;
use GoPhp\GoType\FuncType;
use GoPhp\GoType\SliceType;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Invokable;
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
final class Func implements Invokable
{
    /** @var FuncBody */
    public readonly \Closure $body;
    public readonly FuncType $type;
    public readonly Environment $enclosure;
    public readonly string $namespace;

    // fixme maybe move
    public readonly ?Param $receiver;
    public ?\Closure $bind = null;

    /**
     * @param FuncBody $body
     */
    public function __construct(
        \Closure $body,
        FuncType $type,
        Environment $enclosure,
        string $namespace,
        ?Param $receiver = null,
    ) {
        $this->body = $body;
        $this->namespace = $namespace;
        $this->type = $type;
        $this->receiver = $receiver;
        $this->enclosure = new Environment(enclosing: $enclosure); // remove?
    }

    public function withReceiver(Param $receiver): self
    {
        return new self(
            $this->body,
            $this->type,
            $this->enclosure,
            $this->namespace,
            $receiver,
        );
    }

    public function bind(AddressableValue $instance): void
    {
        $this->bind = function (Environment $env) use ($instance): void {
            assert_types_compatible($instance->type(), $this->receiver->type);

            $env->defineVar(
                $this->receiver->name,
                $instance,
                $this->receiver->type,
            );
        };
    }

    public function __invoke(Argv $argv): GoValue
    {
        assert_argc($this, $argv, $this->type->arity, $this->type->variadic);

        $env = new Environment(enclosing: $this->enclosure);

        $namedReturns = [];

        if ($this->type->returns->named) {
            foreach ($this->type->returns->iter() as $param) {
                $defaultValue = $param->type->defaultValue();
                $namedReturns[] = $defaultValue;

                $env->defineVar(
                    $param->name,
                    $defaultValue,
                    $param->type,
                );
            }
        }

        if ($this->receiver !== null) {
            if ($this->bind === null) {
                throw InternalError::unreachable($this->receiver);
            }

            ($this->bind)($env);
        }

        foreach ($this->type->params->iter() as $i => $param) {
            if ($param->variadic) {
                $sliceType = new SliceType($param->type);
                $sliceBuilder = SliceBuilder::fromType($sliceType);

                for ($argc = \count($argv); $i < $argc; ++$i) {
                    assert_arg_type($argv[$i], $param->type);

                    $sliceBuilder->pushBlindly($argv[$i]->value);
                }

                if ($param->name === null) {
                    continue;
                }

                $env->defineVar(
                    $param->name,
                    $sliceBuilder->build(),
                    $sliceType,
                );

                break;
            }

            assert_arg_type($argv[$i], $param->type);

            if ($param->name === null) {
                continue;
            }

            $env->defineVar(
                $param->name,
                $argv[$i]->value,
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
                return $this->type->returns->len === 1
                    ? $namedReturns[0]
                    : new TupleValue($namedReturns);
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
