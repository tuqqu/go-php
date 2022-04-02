<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Func;

use GoPhp\Env\Environment;
use GoPhp\Error\OperationError;
use GoPhp\GoType\ValueType;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\NoValue;
use GoPhp\Operator;
use GoPhp\StmtValue\ReturnValue;
use GoPhp\StmtValue\SimpleValue;
use GoPhp\StmtValue\StmtValue;
use GoPhp\Stream\StreamProvider;
use function GoPhp\assert_arg_type;
use function GoPhp\assert_types_compatible;
use function GoPhp\assert_argc;

final class FuncValue implements Func, GoValue
{
    public readonly Signature $signature;
    public readonly Environment $enclosure;

    /** @var \Closure(?Environment): StmtValue */
    public readonly \Closure $body;

    public function __construct(
        \Closure $body,
        Params $params,
        Params $returns,
        Environment $enclosure,
    ) {
        $this->body = $body;
        $this->signature = new Signature($params, $returns);
        $this->enclosure = new Environment(enclosing: $enclosure); // remove?
    }

    //fixme move streams to env
    public function __invoke(StreamProvider $streams, GoValue ...$argv): GoValue
    {
        $env = new Environment(enclosing: $this->enclosure);

        assert_argc($argv, $this->signature->arity);

        $i = 0;
        foreach ($this->signature->params as $param) {
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
                throw new \Exception('must return void');
        }

        if (!$stmtValue instanceof ReturnValue) { //fixme 100%? already
            throw new \Exception('wrong return stmt');
        }

        if ($this->signature->returnArity !== $stmtValue->len) {
            throw new \Exception('wrong return count');
        }

        if ($stmtValue->len === 0) {
            return NoValue::NoValue;
        }

        //fixme refactor
        if ($stmtValue->len === 1) {
            $param = $this->signature->returns[0];
            assert_types_compatible($param->type, $stmtValue->value->type());

            return $stmtValue->value;
        }

        $i = 0;
        // fixme add named returns
        foreach ($this->signature->returns as $param) {
            assert_types_compatible($param->type, $stmtValue->value->values[$i]->type());
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

    public function type(): ValueType
    {
        return $this->signature->type;
    }

    public function operate(Operator $op): never
    {
        throw OperationError::unknownOperator($op, $this);
    }

    public function operateOn(Operator $op, GoValue $rhs): never
    {
        throw OperationError::unknownOperator($op, $this);
    }

    public function mutate(Operator $op, GoValue $rhs): never
    {
        throw OperationError::unknownOperator($op, $this);
    }

    public function equals(GoValue $rhs): BoolValue
    {
        return BoolValue::False; //fixme add nil
    }
}
