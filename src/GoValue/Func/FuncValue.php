<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Func;

use GoParser\Ast\Stmt\BlockStmt;
use GoParser\Ast\Stmt\Stmt;
use GoPhp\Env\Environment;
use GoPhp\GoType\ValueType;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\TupleValue;
use GoPhp\Operator;
use GoPhp\StmtValue\ReturnValue;
use GoPhp\StmtValue\StmtValue;

final class FuncValue implements GoValue
{
    public readonly Signature $signature;
    public readonly Environment $enclosure;
    public readonly BlockStmt $body;

    public function __construct(
        BlockStmt $body, //fixme think of a collection object
        Params $params,
        Params $returns,
        Environment $enclosure,
    ) {
        $this->body = $body;
        $this->signature = new Signature($params, $returns);
        $this->enclosure = new Environment(enclosing: $enclosure);
    }

    /**
     * @param callable(Stmt[], ?Environment): StmtValue $bodyEvaluator
     */
    public function __invoke(callable $bodyEvaluator, GoValue ...$argv): ?TupleValue
    {
        $env = new Environment(enclosing: $this->enclosure);

        if ($this->signature->arity !== \count($argv)) {
            throw new \Exception('wrong number of params');
        }

        $i = 0;
        foreach ($this->signature->params as $param) {
            if (!$param->type->conforms($argv[$i]->type())) {
                throw new \Exception('type error');
            }
            foreach ($param->names ?? [] as $name) {
                $env->defineVar($name, $argv[$i++], $param->type);
            }
        }

        /** @var StmtValue $stmtValue */
        $stmtValue = $bodyEvaluator($this->body, $env);

        if ($stmtValue->isNone()) {
            return null;
        }

        if (!$stmtValue instanceof ReturnValue) {
            throw new \Exception('wrong return stmt');
        }

        $i = 0;
        // fixme add named returns
        foreach ($this->signature->returns as $param) {
            if (!$param->type->conforms($stmtValue->values[$i]->type())) {
                throw new \Exception('type error');
            }
        }

        return new TupleValue($stmtValue->values);
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
        throw new \BadMethodCallException('cannot operate');
    }

    public function operateOn(Operator $op, GoValue $rhs): never
    {
        throw new \BadMethodCallException('cannot operate');
    }

    public function equals(GoValue $rhs): BoolValue
    {
        return BoolValue::False; //fixme add nil
    }
}
