<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Func;

use GoPhp\Env\Environment;
use GoPhp\Error\InternalError;
use GoPhp\Error\OperationError;
use GoPhp\Error\ProgramError;
use GoPhp\GoType\GoType;
use GoPhp\GoType\SliceType;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\NamedTrait;
use GoPhp\GoValue\VoidValue;
use GoPhp\GoValue\Slice\SliceBuilder;
use GoPhp\Operator;
use GoPhp\StmtJump\ReturnJump;
use GoPhp\StmtJump\None;
use GoPhp\StmtJump\StmtJump;
use GoPhp\Stream\StreamProvider;
use function GoPhp\assert_arg_type;
use function GoPhp\assert_nil_comparison;
use function GoPhp\assert_types_compatible;

final class FuncValue implements Func, GoValue
{
    use NamedTrait;

    /** @var \Closure(Environment): StmtJump */
    public readonly \Closure $body;
    public readonly Signature $signature;
    public readonly Environment $enclosure;
    public readonly StreamProvider $streams;

    /**
     * @param \Closure(Environment): StmtJump $body
     */
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
        $env = new Environment(enclosing: $this->enclosure);

        $i = 0;
        //fixme move variadic logic
        foreach ($this->signature->params->iter() as $param) {
            if ($param->variadic) {
                $sliceType = new SliceType($param->type);
                $sliceBuilder = SliceBuilder::fromType($sliceType);

                for ($argc = \count($argv); $i < $argc; ++$i) {
                    assert_arg_type($argv[$i], $param->type, $i);

                    $sliceBuilder->pushBlindly($argv[$i]);
                }

                $env->defineVar(
                    $param->names[0],
                    $sliceBuilder->build(),
                    $sliceType
                );

                break;
            }

            assert_arg_type($argv[$i], $param->type, $i);

            foreach ($param->names as $name) {
                $env->defineVar($name, $argv[$i++], $param->type);
            }
        }

        /** @var StmtJump $stmtJump */
        $stmtJump = ($this->body)($env);

        if ($stmtJump instanceof None) {
            return $this->signature->returnArity === 0 ?
                new VoidValue() :
                throw ProgramError::wrongReturnValueNumber([], $this->signature->returns);
        }

        if (!$stmtJump instanceof ReturnJump) {
            throw new InternalError('Unexpected return statement');
        }

        if ($this->signature->returnArity !== $stmtJump->len) {
            throw ProgramError::wrongReturnValueNumber($stmtJump->values(), $this->signature->returns);
        }

        // void & single & tuple value return
        $values = $stmtJump->values();
        foreach ($this->signature->returns->iter() as $i => $param) {
            assert_types_compatible($param->type, $values[$i]->type());
        }

        return $stmtJump->value;
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
