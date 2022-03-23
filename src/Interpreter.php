<?php

declare(strict_types=1);

namespace GoPhp;

use GoParser\Ast\ConstSpec;
use GoParser\Ast\Expr\BinaryExpr;
use GoParser\Ast\Expr\CallExpr;
use GoParser\Ast\Expr\Expr;
use GoParser\Ast\Expr\FloatLit;
use GoParser\Ast\Expr\FuncType as AstFuncType;
use GoParser\Ast\Expr\GroupExpr;
use GoParser\Ast\Expr\Ident;
use GoParser\Ast\Expr\IntLit;
use GoParser\Ast\Expr\QualifiedTypeName;
use GoParser\Ast\Expr\RawStringLit;
use GoParser\Ast\Expr\RuneLit;
use GoParser\Ast\Expr\SingleTypeName;
use GoParser\Ast\Expr\StringLit;
use GoParser\Ast\Expr\Type as AstType;
use GoParser\Ast\Expr\UnaryExpr;
use GoParser\Ast\File as Ast;
use GoParser\Ast\GroupSpec;
use GoParser\Ast\IdentList;
use GoParser\Ast\ParamDecl;
use GoParser\Ast\Params as AstParams;
use GoParser\Ast\Signature as AstSignature;
use GoParser\Ast\Stmt\AssignmentStmt;
use GoParser\Ast\Stmt\BlockStmt;
use GoParser\Ast\Stmt\ConstDecl;
use GoParser\Ast\Stmt\EmptyStmt;
use GoParser\Ast\Stmt\ExprStmt;
use GoParser\Ast\Stmt\FuncDecl;
use GoParser\Ast\Stmt\IfStmt;
use GoParser\Ast\Stmt\ReturnStmt;
use GoParser\Ast\Stmt\Stmt;
use GoParser\Ast\Stmt\VarDecl;
use GoParser\Ast\VarSpec;
use GoPhp\EntryPoint\EntryPointValidator;
use GoPhp\EntryPoint\MainEntryPoint;
use GoPhp\Env\Environment;
use GoPhp\Env\EnvValue\EnvValue;
use GoPhp\Env\EnvValue\Variable;
use GoPhp\GoType\BasicType;
use GoPhp\GoType\FuncType;
use GoPhp\GoType\TypeFactory;
use GoPhp\GoType\ValueType;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\FloatValue;
use GoPhp\GoValue\Func\FuncValue;
use GoPhp\GoValue\Func\Param;
use GoPhp\GoValue\Func\Params;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\IntValue;
use GoPhp\GoValue\StringValue;
use GoPhp\StmtValue\ReturnValue;
use GoPhp\StmtValue\SimpleValue;
use GoPhp\StmtValue\StmtValue;

final class Interpreter
{
    private State $state = State::DeclEvaluation;
    private Environment $env;
    private ?string $curPackage = null;
    private ?FuncValue $entryPoint = null;

    public function __construct(
        private readonly Ast $ast,
        private array $argv = [],
        private readonly StreamProvider $streamProvider = new StdStreamProvider(),
        private readonly EntryPointValidator $entryPointValidator = new MainEntryPoint(),
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

        if ($this->entryPoint !== null) {
            $this->state = State::EntryPoint;
            ($this->entryPoint)($this->evalBlockStmt(...));
            dump('entry success'); die; // fixme debug
        } else {
            dd($this->env, 'no entry'); // fixme debug
        }

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
                    $stmt instanceof ReturnStmt => $this->evalReturnStmt($stmt),
                    $stmt instanceof AssignmentStmt => $this->evalAssignmentStmt($stmt),

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
                    default => dd(
                        'non-declaration statement outside function body',
                        $stmt
                    ),
                };

                if ($value) {
                    return $value;
                }
        }
    }

    private function evalConstDecl(ConstDecl $decl): SimpleValue
    {
        // fixme add iota support

        $lastValue = null;

        foreach (self::wrapSpecs($decl->spec) as $spec) {
            /** @var ConstSpec $spec */

            $type = null;

            if ($spec->type !== null) {
                $type = self::resolveType($spec->type);
            }

            if (!$type instanceof BasicType) {
                throw new \Exception('const must be of basic type');
            }

            $singular = \count($spec->identList->idents) === 1;

            foreach ($spec->identList->idents as $i => $ident) {
                $value = isset($spec->initList->exprs[$i]) ?
                    $this->evalExpr($spec->initList->exprs[$i]) :
                    null;

                if ($singular) {
                    if ($value === null) {
                        $value = $lastValue;
                    } else {
                        $lastValue = $value;
                    }
                }

                if ($value === null) {
                    throw new \Exception('const does not have init value');
                }

                $this->env->defineConst(
                    $ident->name,
                    $value,
                    ($type ?? $value->type())->reify(),
                );
            }
        }

        return SimpleValue::None;
    }

    private function evalVarDecl(VarDecl $decl): SimpleValue
    {
        foreach (self::wrapSpecs($decl->spec) as $spec) {
            /** @var VarSpec $spec */
            $type = null;

            if ($spec->type !== null) {
                $type = self::resolveType($spec->type);
            }

            $initWithDefaultValue = false;
            if ($spec->initList === null) {
                if ($type === null) {
                    throw new \Exception('type error: must be either ini or type');
                }

                $initWithDefaultValue = true;
            }

            // fixme revisit when func returns tuple
            foreach ($spec->identList->idents as $i => $ident) {
                $value = $initWithDefaultValue ?
                    $type->defaultValue() :
                    $this->evalExpr($spec->initList->exprs[$i]);

                $this->env->defineVar(
                    $ident->name,
                    $value,
                    ($type ?? $value->type())->reify(),
                );
            }
        }

        return SimpleValue::None;
    }

    private function evalFuncDecl(FuncDecl $decl): SimpleValue
    {
        [$params, $returns] = self::resolveParamsFromAstSignature($decl->signature);

        $funcValue = new FuncValue($decl->body, $params, $returns, $this->env); //fixme body null
        $this->env->defineFunc($decl->name->name, $funcValue);

        $this->checkEntryPoint($decl->name->name, $funcValue);

        return SimpleValue::None;
    }

    private function evalCallExpr(CallExpr $expr): GoValue
    {
        $func = $this->evalExpr($expr->expr);

        if (!$func instanceof FuncValue) {
            throw new \Exception('call error');
        }

        $argv = [];
        foreach ($expr->args->exprs as $arg) {
            $argv[] = $this->evalExpr($arg);
        }

        return $func($this->evalBlockStmt(...), ...$argv);
    }

    private function evalEmptyStmt(EmptyStmt $stmt): SimpleValue
    {
        return SimpleValue::None;
    }

    private function evalExprStmt(ExprStmt $stmt): SimpleValue
    {
        $this->evalExpr($stmt->expr);

        return SimpleValue::None;
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
            dump($this->env);

            return $stmtVal;
        });
    }

    /**
     * @var callable(): SimpleValue $code
     */
    private function evalWithEnvWrap(?Environment $env, callable $code): StmtValue
    {
        $prevEnv = $this->env;
        $this->env = $env ?? new Environment($this->env);
        $stmtValue = $code();
        $this->env = $prevEnv;

        return $stmtValue;
    }

    private function evalReturnStmt(ReturnStmt $stmt): ReturnValue
    {
        $values = [];
        foreach ($stmt->exprList->exprs as $expr) {
            $values[] = $this->evalExpr($expr);
        }

        return new ReturnValue($values);
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

            return SimpleValue::None;
        });
    }

    private function evalAssignmentStmt(AssignmentStmt $stmt): SimpleValue
    {
        $lhs = [];
        $rhs = [];
        foreach ($stmt->lhs->exprs as $expr) {
            $envValue = $this->getEnvValue($expr);
            if (!$envValue instanceof Variable) {
                throw new \InvalidArgumentException('cannot modify non-vars');
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
                default => throw new \Exception('wrong operator'),
            };

            $this->env->assign($lhs[$i]->name, $newValue);
        }

        return SimpleValue::None;
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
            $expr instanceof CallExpr => $this->evalCallExpr($expr),

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
        return IntValue::fromString($lit->digits, BasicType::UntypedInt);
    }

    private function evalFloatLit(FloatLit $lit): FloatValue
    {
        return FloatValue::fromString($lit->digits, BasicType::UntypedFloat);
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

    private function checkEntryPoint(string $name, FuncValue $funcValue): void
    {
        if (
            !isset($this->entryPoint) &&
            $this->entryPointValidator->validate(
                $this->curPackage ?? '',
                $name,
                $funcValue->signature,
            )
        ) {
            $this->entryPoint = $funcValue;
        }
    }

    private static function wrapSpecs(VarSpec|ConstSpec|GroupSpec $spec): iterable
    {
        return $spec->isGroup() ?
            yield from $spec->specs :
            yield $spec;
    }

    // fixme maybe must be done lazily
    private static function resolveType(AstType $type): ValueType
    {
        $resolved = null;

        switch (true) {
            case $type instanceof SingleTypeName:
                $resolved = TypeFactory::tryFrom($type->name);
                break;
            case $type instanceof QualifiedTypeName:
                $resolved = TypeFactory::tryFrom(self::resolveQualifiedTypeName($type));
                break;
            case $type instanceof AstFuncType:
                $resolved = self::resolveTypeFromAstSignature($type->signature);
                break;
        }

        if ($resolved === null) {
            throw new \InvalidArgumentException('unresolved type'); //fixme
        }

        return $resolved;
    }

    private static function resolveTypeFromAstSignature(AstSignature $signature): FuncType
    {
        return new FuncType(...self::resolveParamsFromAstSignature($signature));
    }

    /**
     * @return array{Params, Params}
     */
    private static function resolveParamsFromAstSignature(AstSignature $signature): array
    {
        return [
            new Params(self::resolveParamsFromAstParams($signature->params)),
            new Params(match (true) {
                $signature->result === null => [],
                $signature->result instanceof AstType => [new Param(self::resolveType($signature->result))],
                $signature->result instanceof AstParams => self::resolveParamsFromAstParams($signature->result),
            }),
        ];
    }

    private static function resolveParamsFromAstParams(AstParams $params): array
    {
        return \array_map(self::paramFromAstParamDecl(...), $params->paramList);
    }

    private static function paramFromAstParamDecl(ParamDecl $paramDecl): Param
    {
        return new Param(
            self::resolveType($paramDecl->type),
            self::arrayFromIdents($paramDecl->identList), // fixme maybe anon option for perf
            $paramDecl->ellipsis !== null,
        );
    }

    private static function arrayFromIdents(IdentList $identList): array
    {
        return \array_map(static fn (Ident $ident): string => $ident->name, $identList->idents);
    }

    private static function resolveQualifiedTypeName(QualifiedTypeName $typeName): string
    {
        return \sprintf('%s.%s', $typeName->packageName->name, $typeName->typeName->name);
    }
}
