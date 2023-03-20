<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Func;

use Closure;
use GoPhp\Argv;
use GoPhp\Env\Environment;
use GoPhp\Error\InternalError;
use GoPhp\Error\RuntimeError;
use GoPhp\GoType\FuncType;
use GoPhp\GoType\PointerType;
use GoPhp\GoType\SliceType;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\PointerValue;
use GoPhp\GoValue\Slice\SliceBuilder;
use GoPhp\GoValue\TupleValue;
use GoPhp\GoValue\VoidValue;
use GoPhp\StmtJump\None;
use GoPhp\StmtJump\ReturnJump;
use GoPhp\StmtJump\StmtJump;

use function count;
use function GoPhp\assert_arg_type;
use function GoPhp\assert_argc;
use function GoPhp\assert_types_compatible;

/**
 * @psalm-type FuncBody = Closure(Environment, string): StmtJump
 */
final class Func
{
    public readonly FuncType $type;

    /** @var FuncBody */
    private readonly Closure $body;
    private readonly Environment $enclosure;
    private readonly string $namespace;
    private readonly ?Receiver $receiver;
    private ?AddressableValue $boundInstance = null;

    /**
     * @param FuncBody $body
     */
    public function __construct(
        Closure $body,
        FuncType $type,
        Environment $enclosure,
        ?Receiver $receiver,
        string $namespace,
    ) {
        $this->body = $body;
        $this->namespace = $namespace;
        $this->type = $type;
        $this->receiver = $receiver;
        $this->enclosure = new Environment(enclosing: $enclosure); // remove?
    }

    public function bind(AddressableValue $instance): void
    {
        $this->boundInstance = $instance;
    }

    public function __invoke(Argv $argv): GoValue
    {
        assert_argc($this, $argv, $this->type->arity, $this->type->variadic);

        $env = new Environment(enclosing: $this->enclosure);

        $namedReturns = [];

        if ($this->type->returns->named) {
            foreach ($this->type->returns->iter() as $param) {
                $defaultValue = $param->type->zeroValue();
                $namedReturns[] = $defaultValue;

                /** @var string $param->name */
                $env->defineVar(
                    $param->name,
                    $defaultValue,
                    $param->type,
                );
            }
        }

        $this->doBind($env);

        foreach ($this->type->params->iter() as $i => $param) {
            if ($param->variadic) {
                $sliceType = new SliceType($param->type);
                $sliceBuilder = SliceBuilder::fromType($sliceType);

                for ($argc = count($argv); $i < $argc; ++$i) {
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
            return $this->type->returnArity === ReturnJump::LEN_VOID
                ? new VoidValue()
                : throw RuntimeError::wrongReturnValueNumber([], $this->type->returns);
        }

        if (!$stmtJump instanceof ReturnJump) {
            throw InternalError::unreachable($stmtJump);
        }

        if ($this->type->returnArity !== $stmtJump->len) {
            // named return: single & tuple value
            if ($stmtJump->len === ReturnJump::LEN_VOID && !empty($namedReturns)) {
                return $this->type->returns->len === ReturnJump::LEN_SINGLE
                    ? $namedReturns[0]
                    : new TupleValue($namedReturns);
            }

            throw RuntimeError::wrongReturnValueNumber($stmtJump->values(), $this->type->returns);
        }

        // void & single & tuple value return
        $values = $stmtJump->values();

        foreach ($this->type->returns->iter() as $i => $param) {
            assert_types_compatible($param->type, $values[$i]->type());
        }

        return $stmtJump->value;
    }

    public function zeroReturnValue(): GoValue
    {
        $zeroValues = [];
        foreach ($this->type->returns->iter() as $return) {
            $zeroValues[] = $return->type->zeroValue();
        }

        return match (count($zeroValues)) {
            ReturnJump::LEN_VOID => new VoidValue(),
            ReturnJump::LEN_SINGLE => $zeroValues[0],
            default => new TupleValue($zeroValues),
        };
    }

    private function doBind(Environment $env): void
    {
        if ($this->receiver === null || $this->receiver->name === null) {
            return;
        }

        if ($this->boundInstance === null) {
            throw InternalError::unreachable($this->receiver);
        }

        $boundInstance = $this->boundInstance instanceof PointerValue
            ? $this->boundInstance->getPointsTo()
            : $this->boundInstance;

        $receiverType = $this->receiver->type;

        if ($boundInstance instanceof PointerValue) {
            $boundInstance = $boundInstance->getPointsTo();
        }

        if ($receiverType instanceof PointerType) {
            $receiverType = $receiverType->pointsTo;
        } else {
            $boundInstance = $boundInstance->copy();
        }

        $env->defineVar(
            $this->receiver->name,
            $boundInstance,
            $receiverType,
        );
    }
}
