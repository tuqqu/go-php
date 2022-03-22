<?php

declare(strict_types=1);

namespace GoPhp;

use GoParser\Ast\ConstSpec;
use GoParser\Ast\Expr\BinaryExpr;
use GoParser\Ast\Expr\Expr;
use GoParser\Ast\Expr\FloatLit;
use GoParser\Ast\Expr\GroupExpr;
use GoParser\Ast\Expr\Ident;
use GoParser\Ast\Expr\IntLit;
use GoParser\Ast\Expr\RawStringLit;
use GoParser\Ast\Expr\RuneLit;
use GoParser\Ast\Expr\StringLit;
use GoParser\Ast\Expr\UnaryExpr;
use GoParser\Ast\File as Ast;
use GoParser\Ast\GroupSpec;
use GoParser\Ast\Stmt\AssignmentStmt;
use GoParser\Ast\Stmt\BlockStmt;
use GoParser\Ast\Stmt\ConstDecl;
use GoParser\Ast\Stmt\EmptyStmt;
use GoParser\Ast\Stmt\ExprStmt;
use GoParser\Ast\Stmt\FuncDecl;
use GoParser\Ast\Stmt\IfStmt;
use GoParser\Ast\Stmt\Stmt;
use GoParser\Ast\Stmt\VarDecl;
use GoParser\Ast\VarSpec;
use GoPhp\Env\Environment;
use GoPhp\Env\EnvValue\EnvValue;
use GoPhp\Env\EnvValue\Func;
use GoPhp\Env\EnvValue\Variable;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\StringValue;
use GoPhp\GoValue\FloatValue;
use GoPhp\GoValue\IntValue;
use GoPhp\GoValue\ValueType;
use Symfony\Component\String\Exception\InvalidArgumentException;

final class Interpreter
{
    private const ENTRY_POINT_PACKAGE_NAME = 'main';
    private const ENTRY_POINT_FUNC_NAME = 'main';

    private State $state = State::DeclEvaluation;
    private Environment $env;
    private ?string $curPackage = null;
    private ?Func $entryPoint = null;

    public function __construct(
        private readonly Ast $ast,
        private array $argv = [],
        private readonly StreamProvider $streamProvider = new StdStreamProvider(),
    )
    {
        $this->env = new Environment();
    }

    public function run(): ExecCode
    {
        $this->curPackage = $this->ast->package->identifier->name;

        foreach ($this->ast->decls as $decl) {
            $this->evalStmt($decl);
        }

        dd($this->env);

        return ExecCode::Success;
    }

    private function evalStmt(Stmt $stmt): StmtValue
    {
        switch ($this->state) {
            case State::EntryPoint:
                $value = match (true) {
                    $stmt instanceof EmptyStmt => $this->evalEmptyStmt($stmt),
                    $stmt instanceof ExprStmt => $this->evalExprStmt($stmt),
                    $stmt instanceof BlockStmt => $this->evalBlockStmt($stmt),
                    $stmt instanceof IfStmt => $this->evalIfStmt($stmt),

                    $stmt instanceof ConstDecl => $this->evalConstDecl($stmt),
                    $stmt instanceof VarDecl => $this->evalVarDecl($stmt),
                    $stmt instanceof FuncDecl => throw new \Exception('Func decl in a func scope'),

                    default => null,
                };

                if ($value) {
                    return $value;
                }
                break;
            case State::DeclEvaluation:
                $value = match (true) {
                    $stmt instanceof ConstDecl => $this->evalConstDecl($stmt),
                    $stmt instanceof VarDecl => $this->evalVarDecl($stmt),
                    $stmt instanceof FuncDecl => $this->evalFuncDecl($stmt),

                    $stmt instanceof IfStmt => $this->evalIfStmt($stmt), //fixme
                    $stmt instanceof EmptyStmt => $this->evalEmptyStmt($stmt), //fixme
                    $stmt instanceof BlockStmt => $this->evalBlockStmt($stmt),
                    $stmt instanceof AssignmentStmt => $this->evalAssignmentStmt($stmt), //fixme
                    default => dd($stmt),
                };

                if ($value) {
                    return $value;
                }
        }
    }

    private function evalConstDecl(ConstDecl $decl): StmtValue
    {
        /** @var ConstSpec[] $specs */
        $specs = [];
        if ($decl->spec instanceof GroupSpec) {
            $specs = [...$specs, ...$decl->spec->specs];
        } else {
            $specs[] = $decl->spec;
        }

        foreach ($specs as $spec) {
            $values = [];

            foreach ($spec->initList->exprs as $expr) {
                $value = $this->evalExpr($expr);
                $values[] = $value;
            }

            $names = [];
            foreach ($spec->identList->idents as $ident) {
                $names[] = $ident->name;
            }
        }

        $valLen = \count($values);
        $namesLen = \count($names);
        if ($valLen > $namesLen) {
            throw new \Exception('Wrong length'); //fixme
        }

        $lastValue = $values[$valLen-1];
        foreach ($names as $i => $name) {
            $this->env->defineConst($name, $values[$i] ?? $lastValue);
        }

        return StmtValue::None;
    }

    // fixme unite with const decl
    private function evalVarDecl(VarDecl $decl): StmtValue
    {
        /** @var VarSpec[] $specs */
        $specs = [];
        if ($decl->spec instanceof GroupSpec) {
            $specs = [...$specs, ...$decl->spec->specs];
        } else {
            $specs[] = $decl->spec;
        }

        foreach ($specs as $spec) {
            $values = [];
            foreach ($spec->initList->exprs as $expr) {
                $value = $this->evalExpr($expr);
                $values[] = $value;
            }

            $names = [];
            foreach ($spec->identList->idents as $ident) {
                $names[] = $ident->name;
            }
        }

        $valLen = \count($values);
        $namesLen = \count($names);
        if ($valLen > $namesLen) {
            throw new \Exception('wrong len');
        }

        $lastValue = $values[$valLen-1];
        foreach ($names as $i => $name) {
            $this->env->defineVar($name, $values[$i] ?? $lastValue); //todo check this last value
        }

        return StmtValue::None;
    }

    private function evalFuncDecl(FuncDecl $decl): StmtValue
    {
        // fixme check entrypoint status
        $this->evalBlockStmt($decl->body);

        return StmtValue::None;
    }

    private function evalEmptyStmt(EmptyStmt $stmt): StmtValue
    {
        return StmtValue::None;
    }

    private function evalExprStmt(ExprStmt $stmt): StmtValue
    {
        $this->evalExpr($stmt->expr);

        return StmtValue::None;
    }

    private function evalBlockStmt(BlockStmt $blockStmt, ?Environment $env = null): StmtValue
    {
        return $this->evalWithEnvWrap($env, function () use ($blockStmt): StmtValue {
            foreach ($blockStmt->stmtList->stmts as $stmt) {
                $stmtVal = $this->evalStmt($stmt);

                if (!$stmtVal->isNone()) {
                    break;
                }
            }

            //fixme debug
            dd($this->env);

            return $stmtVal;
        });
    }

    /**
     * @var callable(): StmtValue $code
     */
    private function evalWithEnvWrap(?Environment $env, callable $code): StmtValue
    {
        $prevEnv = $this->env;
        $this->env = $env ?? new Environment($this->env);
        $stmtValue = $code();
        $this->env = $prevEnv;

        return $stmtValue;
    }

    private function evalIfStmt(IfStmt $stmt): StmtValue
    {
        return $this->evalWithEnvWrap(null, function () use ($stmt): StmtValue {
            if ($stmt->init !== null) {
                $this->evalStmt($stmt->init);
            }

            $condition = $this->evalExpr($stmt->condition);

            if ($this->isTrue($condition)) {
                return $this->evalBlockStmt($stmt->ifBody);
            }

            if ($stmt->elseBody !== null) {
                return $this->evalStmt($stmt->elseBody);
            }

            return StmtValue::None;
        });
    }

    private function evalAssignmentStmt(AssignmentStmt $stmt): StmtValue
    {
        $lhs = [];
        $rhs = [];
        foreach ($stmt->lhs->exprs as $expr) {
            $envValue = $this->getEnvValue($expr);
            if (!$envValue instanceof Variable) {
                throw new InvalidArgumentException('cannot modify non-vars');
            }

            $lhs[] = $envValue;
        }

        foreach ($stmt->rhs->exprs as $expr) {
            $rhs[] = $this->evalExpr($expr);
        }

        $assignLen = \count($lhs);
        for ($i = 0; $i < $assignLen; ++$i) {
            $op = Operator::fromAst($stmt->op);

            $newValue = match (true) {
                $op === Operator::Eq => $rhs[$i],
                $op->isCompound() => $lhs[$i]->value->operateOn($op->disjoin(), $rhs[$i]),
                default => throw new \Exception('wtf'),
            };

            $this->env->assign($lhs[$i]->name, $newValue);
        }

        dump(
            $lhs,
            $rhs,
            $this->env
        );

        return StmtValue::None;
    }

    private function evalExpr(Expr $expr): GoValue
    {
        return match (true) {
            // literals
            $expr instanceof RuneLit => $this->evalRuneLit($expr),
            $expr instanceof StringLit => $this->evalStringLit($expr),
            $expr instanceof RawStringLit => $this->evalStringLit($expr),
            $expr instanceof IntLit => $this->evalIntLit($expr),
            $expr instanceof FloatLit => $this->evalFloatLit($expr),
            $expr instanceof UnaryExpr => $this->evalUnaryExpr($expr),
            $expr instanceof BinaryExpr => $this->evalBinaryExpr($expr),
            $expr instanceof GroupExpr => $this->evalGroupExpr($expr),
            $expr instanceof Ident => $this->evalIdent($expr),

            // fixme debug
            default => dd($expr),
        };
    }

    private function getEnvValue(Expr $expr): EnvValue
    {
        return match (true) {
            // pointers
            $expr instanceof Ident => $this->env->get($expr->name),

            // fixme debug
            default => dd($expr),
        };
    }

    private function evalRuneLit(RuneLit $lit): IntValue
    {
        return IntValue::fromRune($lit->rune);
    }

    private function evalStringLit(StringLit $lit): StringValue
    {
        return new StringValue($lit->str);
    }

    private function evalIntLit(IntLit $lit): IntValue
    {
        return IntValue::fromString($lit->digits, ValueType::Int);
    }

    private function evalFloatLit(FloatLit $lit): FloatValue
    {
        return FloatValue::fromString($lit->digits, ValueType::Float32);
    }

    private function evalBinaryExpr(BinaryExpr $expr): GoValue
    {
        return $this
            ->evalExpr($expr->lExpr)
            ->operateOn(
                Operator::fromAst($expr->op),
                $this->evalExpr($expr->rExpr),
            );
    }

    private function evalUnaryExpr(UnaryExpr $expr): GoValue
    {
        return $this
            ->evalExpr($expr->expr)
            ->operate(Operator::fromAst($expr->op));
    }

    private function evalGroupExpr(GroupExpr $expr): GoValue
    {
        return $this->evalExpr($expr->expr);
    }

    private function evalIdent(Ident $ident): GoValue
    {
        // fixme add builtin.go with predefined idents
        if ($ident->name === 'true') return BoolValue::fromBool(true);
        if ($ident->name === 'false') return BoolValue::fromBool(false);
        if ($ident->name === 'iota') return BoolValue::fromBool(false); //todo

        return $this->env->get($ident->name)->value;
    }

    private function isTrue(GoValue $value): bool
    {
        if (!$value instanceof BoolValue) {
            throw new \InvalidArgumentException('Must be bool'); // fixme
        }

        return $value->unwrap();
    }

    // fixme
    private function checkEntryPointStatus(Func $func): void
    {
        if (
            $this->curPackage === self::ENTRY_POINT_PACKAGE_NAME &&
            $func->name === self::ENTRY_POINT_FUNC_NAME
        ) {
            if ($this->entryPoint !== null) {
                throw new \Exception('two main functions');
            }

            // fixme validate signature

            $this->entryPoint = $func;
        }
    }
}
