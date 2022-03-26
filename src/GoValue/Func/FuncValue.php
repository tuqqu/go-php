<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Func;

use GoPhp\Env\Environment;
use GoPhp\GoType\ValueType;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\NoValue;
use GoPhp\GoValue\TupleValue;
use GoPhp\Operator;
use GoPhp\StmtValue\ReturnValue;
use GoPhp\StmtValue\StmtValue;
use GoPhp\StreamProvider;

final class FuncValue implements GoValue
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

    public function __invoke(StreamProvider $streams, GoValue ...$argv): NoValue|TupleValue
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
        $stmtValue = ($this->body)($env);

        if ($stmtValue->isNone()) {
            return NoValue::NoValue;
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
