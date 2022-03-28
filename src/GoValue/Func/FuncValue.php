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
use GoPhp\Stream\StreamProvider;

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
            return $this->signature->returnArity === 0 ?
                NoValue::NoValue :
                throw new \Exception('must return void');
        }

        if (!$stmtValue instanceof ReturnValue) { //fixme 100%? already
            throw new \Exception('wrong return stmt');
        }

        if ($this->signature->returnArity !== ($stmtValue->values?->len ?? 0)) {
            throw new \Exception('wrong return count');
        }

        $i = 0;
        // fixme add named returns
        foreach ($this->signature->returns as $param) {
            if (!$param->type->conforms($stmtValue->values->values[$i]->type())) {
                throw new \Exception('type error');
            }
        }

        return $stmtValue->values;
    }

    public function toString(): string
    {
        throw new \BadMethodCallException('cannot operate');
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

    public function mutate(Operator $op, GoValue $rhs): never
    {
        throw new \BadMethodCallException('cannot operate');
    }

    public function equals(GoValue $rhs): BoolValue
    {
        return BoolValue::False; //fixme add nil
    }
}
