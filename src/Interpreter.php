<?php

declare(strict_types=1);

namespace GoPhp;

use GoParser\Ast\ConstSpec;
use GoParser\Ast\Expr\ArrayType as AstArrayType;
use GoParser\Ast\Expr\SliceType as AstSliceType;
use GoParser\Ast\Expr\BinaryExpr;
use GoParser\Ast\Expr\CallExpr;
use GoParser\Ast\Expr\CompositeLit;
use GoParser\Ast\Expr\Expr;
use GoParser\Ast\Expr\FloatLit;
use GoParser\Ast\Expr\FuncType as AstFuncType;
use GoParser\Ast\Expr\GroupExpr;
use GoParser\Ast\Expr\Ident;
use GoParser\Ast\Expr\IndexExpr;
use GoParser\Ast\Expr\IntLit;
use GoParser\Ast\Expr\QualifiedTypeName;
use GoParser\Ast\Expr\RawStringLit;
use GoParser\Ast\Expr\RuneLit;
use GoParser\Ast\Expr\SingleTypeName;
use GoParser\Ast\Expr\StringLit;
use GoParser\Ast\Expr\Type;
use GoParser\Ast\Expr\Type as AstType;
use GoParser\Ast\Expr\UnaryExpr;
use GoParser\Ast\File as Ast;
use GoParser\Ast\ForClause;
use GoParser\Ast\GroupSpec;
use GoParser\Ast\IdentList;
use GoParser\Ast\ParamDecl;
use GoParser\Ast\Params as AstParams;
use GoParser\Ast\Punctuation;
use GoParser\Ast\RangeClause;
use GoParser\Ast\Signature as AstSignature;
use GoParser\Ast\Spec;
use GoParser\Ast\Stmt\AssignmentStmt;
use GoParser\Ast\Stmt\BlockStmt;
use GoParser\Ast\Stmt\BreakStmt;
use GoParser\Ast\Stmt\ConstDecl;
use GoParser\Ast\Stmt\ContinueStmt;
use GoParser\Ast\Stmt\EmptyStmt;
use GoParser\Ast\Stmt\ExprStmt;
use GoParser\Ast\Stmt\ForStmt;
use GoParser\Ast\Stmt\FuncDecl;
use GoParser\Ast\Stmt\IfStmt;
use GoParser\Ast\Stmt\IncDecStmt;
use GoParser\Ast\Stmt\ReturnStmt;
use GoParser\Ast\Stmt\ShortVarDecl;
use GoParser\Ast\Stmt\Stmt;
use GoParser\Ast\Stmt\VarDecl;
use GoParser\Ast\VarSpec;
use GoParser\Lexer\Token;
use GoParser\Parser;
use GoPhp\EntryPoint\EntryPointValidator;
use GoPhp\EntryPoint\MainEntryPoint;
use GoPhp\Env\Builtin\BuiltinProvider;
use GoPhp\Env\Builtin\StdBuiltinProvider;
use GoPhp\Env\Environment;
use GoPhp\Error\DefinitionError;
use GoPhp\Error\InternalError;
use GoPhp\Error\OperationError;
use GoPhp\Error\ProgramError;
use GoPhp\Error\TypeError;
use GoPhp\Error\ValueError;
use GoPhp\GoType\ArrayType;
use GoPhp\GoType\BasicType;
use GoPhp\GoType\FuncType;
use GoPhp\GoType\SliceType;
use GoPhp\GoType\TypeFactory;
use GoPhp\GoType\ValueType;
use GoPhp\GoValue\Array\ArrayBuilder;
use GoPhp\GoValue\Array\ArrayValue;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\Float\UntypedFloatValue;
use GoPhp\GoValue\Func\FuncValue;
use GoPhp\GoValue\Invocable;
use GoPhp\GoValue\Func\Param;
use GoPhp\GoValue\Func\Params;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Int\BaseIntValue;
use GoPhp\GoValue\Int\Int32Value;
use GoPhp\GoValue\Int\UntypedIntValue;
use GoPhp\GoValue\Sequence;
use GoPhp\GoValue\Slice\SliceBuilder;
use GoPhp\GoValue\StringValue;
use GoPhp\GoValue\TupleValue;
use GoPhp\StmtValue\ReturnValue;
use GoPhp\StmtValue\SimpleValue;
use GoPhp\StmtValue\StmtValue;
use GoPhp\Stream\StdStreamProvider;
use GoPhp\Stream\StreamProvider;

final class Interpreter
{
    private State $state = State::DeclEvaluation;
    private Environment $env;
    private ?string $curPackage = null;
    private ?FuncValue $entryPoint = null;

    public function __construct(
        private readonly Ast $ast,
        private array $argv = [],
        private readonly StreamProvider $streams = new StdStreamProvider(),
        private readonly EntryPointValidator $entryPointValidator = new MainEntryPoint(),
        BuiltinProvider $builtin = new StdBuiltinProvider(),
    ) {
        $this->env = new Environment($builtin->provide());
    }

    public static function fromString(
        string $src,
        array $argv = [],
        StreamProvider $streams = new StdStreamProvider(),
        EntryPointValidator $entryPointValidator = new MainEntryPoint(),
        BuiltinProvider $builtin = new StdBuiltinProvider(),
    ): self {
        // fixme add onerror
        $parser = new Parser($src);
        $ast = $parser->parse();

        if ($parser->hasErrors()) {
            // fixme handle errs
            dump('has parse errs');
            foreach ($parser->getErrors() as $error) {
                dump((string)$error);
            }
            die;
        }

        return new self($ast, $argv, $streams, $entryPointValidator, $builtin);
    }

    public function run(): ExecCode
    {
        $this->curPackage = $this->ast->package->identifier->name;

        try {
            foreach ($this->ast->decls as $decl) {
                $this->evalStmt($decl);
            }
        } catch (\Throwable $throwable) {
            $this->streams->stderr()->writeln($throwable->getMessage());

            return ExecCode::Failure;
        }

        if ($this->entryPoint !== null) {
            $this->state = State::EntryPoint;

            try {
                ($this->entryPoint)($this->streams, ...$this->argv);
            } catch (\Throwable $throwable) {
                dump($throwable);
                $this->streams->stderr()->writeln($throwable->getMessage());

                return ExecCode::Failure;
            }
        } else {
            dd($this->env, 'no entry'); // fixme debug
        }

        return ExecCode::Success;
    }

    private function evalStmt(Stmt $stmt): StmtValue
    {
        return match ($this->state) {
            State::EntryPoint => match (true) {
                $stmt instanceof EmptyStmt => $this->evalEmptyStmt($stmt),
                $stmt instanceof BreakStmt => $this->evalBreakStmt($stmt),
                $stmt instanceof ContinueStmt => $this->evalContinueStmt($stmt),
                $stmt instanceof ExprStmt => $this->evalExprStmt($stmt),
                $stmt instanceof BlockStmt => $this->evalBlockStmt($stmt),
                $stmt instanceof IfStmt => $this->evalIfStmt($stmt),
                $stmt instanceof ForStmt => $this->evalForStmt($stmt),
                $stmt instanceof IncDecStmt => $this->evalIncDecStmt($stmt),
                $stmt instanceof ReturnStmt => $this->evalReturnStmt($stmt),
                $stmt instanceof AssignmentStmt => $this->evalAssignmentStmt($stmt),
                $stmt instanceof ShortVarDecl => $this->evalShortVarDeclStmt($stmt),
                $stmt instanceof ConstDecl => $this->evalConstDeclStmt($stmt),
                $stmt instanceof VarDecl => $this->evalVarDeclStmt($stmt),
                $stmt instanceof FuncDecl => throw new ProgramError('Function declaration in a function scope'),
                default => throw new ProgramError(\sprintf('Unknown statement %s', $stmt::class)),
             },
            State::DeclEvaluation => match (true) {
                $stmt instanceof ConstDecl => $this->evalConstDeclStmt($stmt),
                $stmt instanceof VarDecl => $this->evalVarDeclStmt($stmt),
                $stmt instanceof FuncDecl => $this->evalFuncDeclStmt($stmt),
                default => throw new ProgramError('Non-declaration on a top-level'),
            },
        };
    }

    private function evalConstDeclStmt(ConstDecl $decl): SimpleValue
    {
        // fixme add iota support

        $lastValue = null;

        foreach (self::wrapSpecs($decl->spec) as $spec) {
            /** @var ConstSpec $spec */
            $type = null;
            if ($spec->type !== null) {
                $type = $this->resolveType($spec->type);
            }

            if ($type !== null && !$type instanceof BasicType) {
                throw DefinitionError::constantExpectsBasicType($type);
            }

            $singular = \count($spec->identList->idents) === 1;

            foreach ($spec->identList->idents as $i => $ident) {
                $value = isset($spec->initList->exprs[$i]) ?
                    $this->tryEvalConstExpr($spec->initList->exprs[$i]) :
                    null;

                if ($singular) {
                    if ($value === null) {
                        $value = $lastValue;
                    } else {
                        $lastValue = $value;
                    }
                }

                if ($value === null) {
                    throw DefinitionError::uninitialisedConstant($ident->name);
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

    private function evalVarDeclStmt(VarDecl $decl): SimpleValue
    {
        foreach (self::wrapSpecs($decl->spec) as $spec) {
            /** @var VarSpec $spec */
            $type = null;
            if ($spec->type !== null) {
                $type = $this->resolveType($spec->type);
            }

            $initWithDefaultValue = false;
            if ($spec->initList === null) {
                if ($type === null) {
                    throw DefinitionError::uninitilisedVarWithNoType();
                }
                $initWithDefaultValue = true;
            }

            $values = [];
            $identsLen = \count($spec->identList->idents);
            if ($initWithDefaultValue) {
                for ($i = 0; $i < $identsLen; ++$i) {
                    $values[] = $type->defaultValue();
                }
            } else {
                $value = $this->evalExpr($spec->initList->exprs[0]);

                if ($value instanceof TupleValue) {
                    $exprLen = \count($spec->initList->exprs);
                    if ($exprLen !== 1) {
                        throw ValueError::multipleValueInSingleContext();
                    }

                    foreach ($value->values as $val) {
                        $values[] = $val;
                    }

                    $valuesLen = \count($values);
                    if ($valuesLen !== $identsLen) {
                        throw DefinitionError::assignmentMismatch($identsLen, $valuesLen);
                    }
                } else {
                    $values[] = $value;
                    for ($i = 1; $i < $identsLen; ++$i) {
                        $value = $this->evalExpr($spec->initList->exprs[$i++]);
                        if ($value instanceof TupleValue) {
                            throw ValueError::multipleValueInSingleContext();
                        }
                        $values[] = $value;
                    }
                }
            }

            foreach ($spec->identList->idents as $i => $ident) {
                $this->env->defineVar(
                    $ident->name,
                    $values[$i]->copy(),
                    ($type ?? $values[$i]->type())->reify(),
                );
            }
        }

        return SimpleValue::None;
    }

    private function evalFuncDeclStmt(FuncDecl $decl): SimpleValue
    {
        [$params, $returns] = $this->resolveParamsFromAstSignature($decl->signature);

        $funcValue = new FuncValue(
            fn (Environment $env) => $this->evalBlockStmt($decl->body, $env),
            $params,
            $returns,
            $this->env
        ); //fixme body null
        $this->env->defineFunc($decl->name->name, $funcValue);

        $this->checkEntryPoint($decl->name->name, $funcValue);

        return SimpleValue::None;
    }

    private function evalCallExpr(CallExpr $expr): GoValue
    {
        $func = $this->evalExpr($expr->expr);

        if (!$func instanceof Invocable) {
            throw OperationError::nonFunctionCall($func);
        }

        $argv = [];
        foreach ($expr->args->exprs as $arg) {
            $argv[] = $this->evalExpr($arg)->copy();
        }

        return $func($this->streams, ...$argv); //fixme tuple
    }

    private function evalIndexExpr(IndexExpr $expr): GoValue
    {
        $array = $this->evalExpr($expr->expr);

        if (!$array instanceof Sequence) {
            throw TypeError::valueOfWrongType($array, 'array');
        }

        $index = $this->evalExpr($expr->index);

        if (!$index instanceof BaseIntValue) {
            throw TypeError::conversionError($index->type(), BasicType::Int);
        }

        return $array->get($index->unwrap());
    }

    private function evalEmptyStmt(EmptyStmt $stmt): SimpleValue
    {
        return SimpleValue::None;
    }

    private function evalBreakStmt(BreakStmt $stmt): SimpleValue
    {
        return SimpleValue::Break;
    }

    private function evalContinueStmt(ContinueStmt $stmt): SimpleValue
    {
        return SimpleValue::Continue;
    }

    private function evalExprStmt(ExprStmt $stmt): SimpleValue
    {
        $this->evalExpr($stmt->expr);

        return SimpleValue::None;
    }

    private function evalBlockStmt(BlockStmt $blockStmt, ?Environment $env = null): StmtValue
    {
        return $this->evalWithEnvWrap($env, function () use ($blockStmt): StmtValue {
            $stmtVal = SimpleValue::None;

            foreach ($blockStmt->stmtList->stmts as $stmt) {
                $stmtVal = $this->evalStmt($stmt);

                if ($stmtVal !== SimpleValue::None) {
                    break;
                }
            }

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
        if (empty($stmt->exprList->exprs)) {
            return ReturnValue::fromVoid();
        }

        $values = [];

        foreach ($stmt->exprList->exprs as $expr) {
            $value = $this->evalExpr($expr);

            if ($value instanceof TupleValue) {
                if (!empty($values)) {
                    throw ValueError::multipleValueInSingleContext();
                }
                return ReturnValue::fromTuple($value);
            }

            $values[] = $value;
        }

        if (\count($values) === 1) {
            return ReturnValue::fromSingle($values[0]);
        }

        return ReturnValue::fromMultiple($values);
    }

    private function evalIfStmt(IfStmt $stmt): StmtValue
    {
        return $this->evalWithEnvWrap(null, function () use ($stmt): StmtValue {
            if ($stmt->init !== null) {
                $this->evalStmt($stmt->init);
            }

            $condition = $this->evalExpr($stmt->condition);

            if (self::isTrue($condition)) {
                return $this->evalBlockStmt($stmt->ifBody);
            }

            if ($stmt->elseBody !== null) {
                return $this->evalStmt($stmt->elseBody);
            }

            return SimpleValue::None;
        });
    }

    private function evalForStmt(ForStmt $stmt): StmtValue
    {
        return $this->evalWithEnvWrap(null, function () use ($stmt): StmtValue {
            switch (true) {
                // for {}
                case $stmt->iteration === null:
                   $condition = null;
                   $post = null;
                   break;
               // for expr {}
                case $stmt->iteration instanceof Expr:
                    $condition = $stmt->iteration;
                    $post = null;
                    break;
                // for expr; expr; expr {}
                case $stmt->iteration instanceof ForClause:
                    if ($stmt->iteration->init !== null) {
                        $this->evalStmt($stmt->iteration->init);
                    }

                    $condition = match (true) {
                        $stmt->iteration->condition === null => null,
                        $stmt->iteration->condition instanceof ExprStmt => $stmt->iteration->condition->expr,
                        default => throw new ProgramError('Unknown for loop condition'),
                    };

                    $post = $stmt->iteration->post ?? null;
                    break;
                // for range {}
                case $stmt->iteration instanceof RangeClause:
                    // todo
                default:
                    throw new ProgramError('Unknown for loop structure');
            }

            while (
                $condition === null
                || self::isTrue($this->evalExpr($condition))
            ) {
                $stmtValue = $this->evalBlockStmt($stmt->body);

                switch (true) {
                    case $stmtValue === SimpleValue::None:
                        break;
                    case $stmtValue === SimpleValue::Continue:
                        if ($post !== null) {
                            $this->evalStmt($post);
                        }
                        continue 2;
                    case $stmtValue === SimpleValue::Break:
                        return SimpleValue::None;
                    case $stmtValue instanceof ReturnValue:
                        return $stmtValue;
                    default:
                        throw new InternalError('Unknown statement value');
                }

                if ($post !== null) {
                    $this->evalStmt($post);
                }
            }

            return SimpleValue::None;
        });
    }

    private function evalIncDecStmt(IncDecStmt $stmt): SimpleValue
    {
        $this
            ->evalExpr($stmt->lhs)
            ->mutate(
                Operator::fromAst($stmt->op),
                new UntypedIntValue(1)
            );

        return SimpleValue::None;
    }

    private function evalAssignmentStmt(AssignmentStmt $stmt): SimpleValue
    {
        // fixme duplicated logic
        $op = Operator::fromAst($stmt->op);
        $compound = $op->isCompound();

        if (!$op->isAssignment()) {
            throw OperationError::expectedAssignmentOperator($op);
        }

        $lhs = [];
        foreach ($stmt->lhs->exprs as $expr) {
            $lhs[] = $this->getValueMutator($expr, $compound);
        }

        $rhs = [];
        $lhsLen = \count($lhs);
        $value = $this->evalExpr($stmt->rhs->exprs[0]);

        if ($value instanceof TupleValue) {
            $rhsLen = \count($stmt->rhs->exprs);
            if ($rhsLen !== 1) {
                throw ValueError::multipleValueInSingleContext();
            }

            foreach ($value->values as $value) {
                $rhs[] = $value;
            }

            $valuesLen = \count($rhs);
            if ($valuesLen !== $lhsLen) {
                throw DefinitionError::assignmentMismatch($lhsLen, $valuesLen);
            }
        } else {
            $rhs[] = $value;
            for ($i = 1; $i < $lhsLen; ++$i) {
                $value = $this->evalExpr($stmt->rhs->exprs[$i]);
                if ($value instanceof TupleValue) {
                    throw ValueError::multipleValueInSingleContext();
                }
                $rhs[] = $value;
            }
        }

        for ($i = 0; $i < $lhsLen; ++$i) {
            if ($compound) {
                $lhs[$i]->mutate($op, $rhs[$i]);
            } else {
                $lhs[$i]->mutate(null, $rhs[$i]->copy());
            }
        }

        return SimpleValue::None;
    }

    private function evalShortVarDeclStmt(ShortVarDecl $stmt): SimpleValue
    {
        // fixme duplicated logic
        $values = [];
        $len = \count($stmt->identList->idents);
        $value = $this->evalExpr($stmt->exprList->exprs[0]);

        if ($value instanceof TupleValue) {
            $exprLen = \count($stmt->exprList->exprs);
            if ($exprLen !== 1) {
                throw ValueError::multipleValueInSingleContext();
            }

            foreach ($value->values as $value) {
                $values[] = $value;
            }

            $valuesLen = \count($values);
            if ($valuesLen !== $len) {
                throw DefinitionError::assignmentMismatch($len, $valuesLen);
            }
        } else {
            $values[] = $value;
            for ($i = 1; $i < $len; ++$i) {
                $value = $this->evalExpr($stmt->exprList->exprs[$i++]);
                if ($value instanceof TupleValue) {
                    throw ValueError::multipleValueInSingleContext();
                }
                $values[] = $value;
            }
        }

        foreach ($stmt->identList->idents as $i => $ident) {
            $this->env->defineVar(
                $ident->name,
                $values[$i]->copy(),
                $values[$i]->type()->reify(),
            );
        }

        return SimpleValue::None;
    }

    private function evalExpr(Expr $expr): GoValue
    {
        $value = $this->tryEvalConstExpr($expr);

        return match (true) {
            // literals
            $value !== null => $value,
            $expr instanceof CallExpr => $this->evalCallExpr($expr),
            $expr instanceof IndexExpr => $this->evalIndexExpr($expr),
            $expr instanceof CompositeLit => $this->evalCompositeLit($expr),

            // fixme debug
            default => dd($expr),
        };
    }

    private function tryEvalConstExpr(Expr $expr): ?GoValue
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
            default => null,
        };
    }

    private function evalConstExpr(Expr $expr): GoValue
    {
        return $this->tryEvalConstExpr($expr) ?? throw new \Exception('cannot eval const expr');
    }

    // fixme introduce type maybe
    private function getValueMutator(Expr $expr, bool $compound): ValueMutator
    {
        return match (true) {
            $expr instanceof Ident => $this->getVarMutator($expr, $compound),
            $expr instanceof IndexExpr => $this->getArrayMutator($expr, $compound),
            // fixme debug
            default => dd($expr),
        };
    }

    private function evalCompositeLit(CompositeLit $lit): GoValue //fixme arrayvalye, slice, map, struct etc...
    {
        //fixme change this in parser
        if (!$lit->type instanceof Type) {
            throw new \Exception('exp type');
        }

        $type = $this->resolveType($lit->type, true);

        if ($type instanceof ArrayType) {
            $builder = ArrayBuilder::fromType($type);

            foreach ($lit->elementList->elements as $element) {
                $builder->push($this->evalExpr($element->element));
            }

            return $builder->build();
        } elseif ($type instanceof SliceType) {
            $builder = SliceBuilder::fromType($type);

            foreach ($lit->elementList->elements as $element) {
                $builder->push($this->evalExpr($element->element));
            }

            return $builder->build();
        }

        throw new \Exception('unknown composite lit');
    }

    private function evalRuneLit(RuneLit $lit): Int32Value
    {
        return Int32Value::fromRune($lit->rune);
    }

    private function evalStringLit(StringLit $lit): StringValue
    {
        return new StringValue($lit->str);
    }

    private function evalIntLit(IntLit $lit): UntypedIntValue
    {
        return UntypedIntValue::fromString($lit->digits);
    }

    private function evalFloatLit(FloatLit $lit): UntypedFloatValue
    {
        return UntypedFloatValue::fromString($lit->digits);
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
        return $this->env->get($ident->name)->unwrap();
    }

    private function getVarMutator(Ident $ident, bool $compound): ValueMutator
    {
        $var = $this->env->getMut($ident->name);

        return ValueMutator::fromEnvVar($var, $compound);
    }

    private function getArrayMutator(IndexExpr $expr, bool $compound): ValueMutator
    {
        if ($expr->expr instanceof IndexExpr) {
            $array = $this->evalIndexExpr($expr->expr);
        } elseif ($expr->expr instanceof Ident) {
            $array = $this->evalIdent($expr->expr);
        } else {
            throw new \Exception('cannot assign');
        }

        if (!$array instanceof ArrayValue) {
            TypeError::valueOfWrongType($array, 'array');
        }

        $index = $this->evalExpr($expr->index);

        if (!$index instanceof BaseIntValue) { //fixme check int type
            TypeError::conversionError($index->type(), BasicType::Int);
        }

        return ValueMutator::fromArrayValue($array, $index, $compound);
    }

    private static function isTrue(GoValue $value): bool
    {
        if (!$value instanceof BoolValue) {
            throw TypeError::valueOfWrongType($value, BasicType::Bool);
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

    /**
     * @return iterable<Spec>
     */
    private static function wrapSpecs(VarSpec|ConstSpec|GroupSpec $spec): iterable
    {
        return $spec->isGroup() ?
            yield from $spec->specs :
            yield $spec;
    }

    // fixme maybe must be done lazily
    private function resolveType(AstType $type, bool $composite = false): ValueType
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
                $resolved = $this->resolveTypeFromAstSignature($type->signature);
                break;
            case $type instanceof AstArrayType:
                $resolved = $this->resolveArrayType($type, $composite);
                break;
            case $type instanceof AstSliceType:
                $resolved = $this->resolveSliceType($type, $composite);
                break;
            default:
                dump($type);
                throw new \InvalidArgumentException('unresolved type'); //fixme
        }

        return $resolved;
    }

    private function resolveTypeFromAstSignature(AstSignature $signature): FuncType
    {
        return new FuncType(...$this->resolveParamsFromAstSignature($signature));
    }

    private function resolveArrayType(AstArrayType $arrayType, bool $composite): ArrayType
    {
        if (
            $arrayType->len instanceof Punctuation &&
            $arrayType->len->value === Token::Ellipsis->value &&
            $composite
        ) {
            $len = null;
        } elseif ($arrayType->len instanceof Expr) {
            $len = $this->evalConstExpr($arrayType->len);

            if (!$len instanceof BaseIntValue) {
                throw TypeError::valueOfWrongType($len, BasicType::Int);
            }
        } else {
            throw new InternalError('Unexpected array length value');
        }

        return new ArrayType(
            $this->resolveType($arrayType->elemType, $composite),
            $len?->unwrap(),
        );
    }

    private function resolveSliceType(AstSliceType $sliceType, bool $composite): SliceType
    {
        return new SliceType($this->resolveType($sliceType->elemType, $composite));
    }

    /**
     * @return array{Params, Params}
     */
    private function resolveParamsFromAstSignature(AstSignature $signature): array
    {
        return [
            new Params($this->resolveParamsFromAstParams($signature->params)),
            new Params(match (true) {
                $signature->result === null => [],
                $signature->result instanceof AstType => [new Param($this->resolveType($signature->result))],
                $signature->result instanceof AstParams => $this->resolveParamsFromAstParams($signature->result),
            }),
        ];
    }

    private function resolveParamsFromAstParams(AstParams $params): array
    {
        return \array_map($this->paramFromAstParamDecl(...), $params->paramList);
    }

    private function paramFromAstParamDecl(ParamDecl $paramDecl): Param
    {
        return new Param(
            $this->resolveType($paramDecl->type),
            $paramDecl->identList === null ?
                [] :
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
