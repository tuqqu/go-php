<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Func;

use GoPhp\Env\Environment;
use GoPhp\Env\EnvMap;
use GoPhp\Error\InternalError;
use GoPhp\Error\OperationError;
use GoPhp\Error\ProgramError;
use GoPhp\GoType\GoType;
use GoPhp\GoType\SliceType;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\NamedTrait;
use GoPhp\GoValue\TupleValue;
use GoPhp\GoValue\VoidValue;
use GoPhp\GoValue\Slice\SliceBuilder;
use GoPhp\Operator;
use GoPhp\StmtJump\ReturnJump;
use GoPhp\StmtJump\None;
use GoPhp\StmtJump\StmtJump;

use function GoPhp\assert_arg_type;
use function GoPhp\assert_argc;
use function GoPhp\assert_nil_comparison;
use function GoPhp\assert_types_compatible;

/**
 * @psalm-type FunctionBody = \Closure(Environment, string): StmtJump
 */
final class FuncValue implements Func, GoValue
{
    use NamedTrait;

    public readonly Signature $signature;
    /** @var FunctionBody */
    private readonly \Closure $body;
    private readonly Environment $enclosure;
    private readonly ?string $namespace;

    /**
     * @param FunctionBody $body
     */
    public function __construct(
        \Closure $body,
        Params $params,
        Params $returns,
        Environment $enclosure,
        ?string $namespace,
    ) {
        $this->body = $body;
        $this->namespace = $namespace;
        $this->signature = new Signature($params, $returns);
        $this->enclosure = new Environment(enclosing: $enclosure); // remove?
    }

    public function __invoke(GoValue ...$argv): GoValue
    {
        assert_argc(
            $argv,
            $this->signature->arity,
            $this->signature->variadic,
            $this->signature->params,
        );

        $env = new Environment(enclosing: $this->enclosure);

        $namedReturns = [];

        if ($this->signature->returns->named) {
            foreach ($this->signature->returns->iter() as $param) {
                $namedReturns[] = $param->name;

                $env->defineVar(
                    $param->name,
                    EnvMap::NAMESPACE_TOP,
                    $param->type->defaultValue(),
                    $param->type,
                );
            }
        }

        foreach ($this->signature->params->iter() as $i => $param) {
            if ($param->variadic) {
                $sliceType = new SliceType($param->type);
                $sliceBuilder = SliceBuilder::fromType($sliceType);

                for ($argc = \count($argv); $i < $argc; ++$i) {
                    assert_arg_type($argv[$i], $param->type, $i);

                    $sliceBuilder->pushBlindly($argv[$i]);
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

            // fixme variadic anon
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
            return $this->signature->returnArity === 0 ?
                new VoidValue() :
                throw ProgramError::wrongReturnValueNumber([], $this->signature->returns);
        }

        if (!$stmtJump instanceof ReturnJump) {
            throw InternalError::unreachable($stmtJump);
        }

        if ($this->signature->returnArity !== $stmtJump->len) {
            // named return: single & tuple value
            if ($stmtJump->len === 0 && !empty($namedReturns)) {
                $namedValues = [];

                foreach ($namedReturns as $namedReturn) {
                    $namedValues[] = $env->get($namedReturn, EnvMap::NAMESPACE_TOP)->unwrap();
                }

                return $this->signature->returns->len === 1
                    ? $namedValues[0]
                    : new TupleValue($namedValues);
            }

            throw ProgramError::wrongReturnValueNumber($stmtJump->values(), $this->signature->returns);
        }

        // void & single & tuple value return
        $values = $stmtJump->values();

        foreach ($this->signature->returns->iter() as $i => $param) {
            assert_types_compatible($param->type, $values[$i]->type());
        }

        return $stmtJump->value;
    }

    public function copy(): self
    {
        return $this;
    }

    public function toString(): string
    {
        throw OperationError::unsupportedOperation(__METHOD__, $this);
    }

    public function unwrap(): callable
    {
        return $this;
    }

    public function type(): GoType
    {
        return $this->signature->type;
    }

    public function operate(Operator $op): never
    {
        throw OperationError::undefinedOperator($op, $this);
    }

    public function operateOn(Operator $op, GoValue $rhs): BoolValue
    {
        assert_nil_comparison($this, $rhs);

        return match ($op) {
            Operator::EqEq => BoolValue::false(),
            Operator::NotEq => BoolValue::true(),
            default => throw OperationError::undefinedOperator($op, $this),
        };
    }

    public function mutate(Operator $op, GoValue $rhs): never
    {
        throw OperationError::undefinedOperator($op, $this);
    }

    public function equals(GoValue $rhs): BoolValue
    {
        return BoolValue::false();
    }
}
