<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Func;

use GoPhp\Env\Environment;
use GoPhp\Error\InternalError;
use GoPhp\Error\OperationError;
use GoPhp\Error\ProgramError;
use GoPhp\GoType\GoType;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\NoValue;
use GoPhp\Operator;
use GoPhp\StmtValue\ReturnValue;
use GoPhp\StmtValue\SimpleValue;
use GoPhp\StmtValue\StmtValue;
use GoPhp\Stream\StreamProvider;
use function GoPhp\assert_arg_type;
use function GoPhp\assert_argc;
use function GoPhp\assert_nil_comparison;
use function GoPhp\assert_types_compatible;

final class FuncValue implements Func, GoValue
{
    public readonly Signature $signature;
    public readonly Environment $enclosure;
    public readonly StreamProvider $streams;

    /** @var \Closure(?Environment): StmtValue */
    public readonly \Closure $body;

    public function __construct(
        \Closure $body,
        Params $params,
        Params $returns,
        Environment $enclosure,
        StreamProvider $streams,
    ) {
        $this->body = $body;
        $this->streams = $streams;
        $this->signature = new Signature($params, $returns);
        $this->enclosure = new Environment(enclosing: $enclosure); // remove?
    }

    public function __invoke(GoValue ...$argv): GoValue
    {
        assert_argc($argv, $this->signature->arity, params: $this->signature->params);

        $env = new Environment(enclosing: $this->enclosure);

        $i = 0;
        foreach ($this->signature->params->iter() as $param) {
            assert_arg_type($argv[$i], $param->type, $i);

            foreach ($param->names as $name) {
                $env->defineVar($name, $argv[$i++], $param->type);
            }
        }

        /** @var StmtValue $stmtValue */
        $stmtValue = ($this->body)($env);

        if ($stmtValue === SimpleValue::None) {
            return $this->signature->returnArity === 0 ?
                NoValue::NoValue :
                throw ProgramError::wrongReturnValueNumber([], $this->signature->returns);
        }

        if (!$stmtValue instanceof ReturnValue) {
            throw new InternalError('Unexpected return statement');
        }

        if ($this->signature->returnArity !== $stmtValue->len) {
            throw ProgramError::wrongReturnValueNumber($stmtValue->values(), $this->signature->returns);
        }

        // void return
        if ($stmtValue->len === 0) {
            return NoValue::NoValue;
        }

        // single & tuple value return
        $i = 0;
        foreach ($this->signature->returns->iter() as $param) {
            assert_types_compatible($param->type, $stmtValue->values()[$i]->type());
        }

        return $stmtValue->value;
    }

    public function copy(): static
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
