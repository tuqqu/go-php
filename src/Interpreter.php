<?php

declare(strict_types=1);

namespace GoPhp;

use GoParser\Ast\ConstSpec;
use GoParser\Ast\DefaultCase;
use GoParser\Ast\Expr\ArrayType as AstArrayType;
use GoParser\Ast\Expr\BinaryExpr;
use GoParser\Ast\Expr\CallExpr;
use GoParser\Ast\Expr\CompositeLit;
use GoParser\Ast\Expr\Expr;
use GoParser\Ast\Expr\FloatLit;
use GoParser\Ast\Expr\FullSliceExpr;
use GoParser\Ast\Expr\FuncType as AstFuncType;
use GoParser\Ast\Expr\GroupExpr;
use GoParser\Ast\Expr\Ident;
use GoParser\Ast\Expr\IndexExpr;
use GoParser\Ast\Expr\IntLit;
use GoParser\Ast\Expr\MapType as AstMapType;
use GoParser\Ast\Expr\PointerType as AstPointerType;
use GoParser\Ast\Expr\QualifiedTypeName;
use GoParser\Ast\Expr\RuneLit;
use GoParser\Ast\Expr\SingleTypeName;
use GoParser\Ast\Expr\SliceExpr;
use GoParser\Ast\Expr\SliceType as AstSliceType;
use GoParser\Ast\Expr\StringLit;
use GoParser\Ast\Expr\Type as AstType;
use GoParser\Ast\Expr\UnaryExpr;
use GoParser\Ast\ExprCaseClause;
use GoParser\Ast\ExprList;
use GoParser\Ast\ExprSwitchCase;
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
use GoParser\Ast\Stmt\DeferStmt;
use GoParser\Ast\Stmt\EmptyStmt;
use GoParser\Ast\Stmt\ExprStmt;
use GoParser\Ast\Stmt\ExprSwitchStmt;
use GoParser\Ast\Stmt\FallthroughStmt;
use GoParser\Ast\Stmt\ForStmt;
use GoParser\Ast\Stmt\FuncDecl;
use GoParser\Ast\Stmt\GotoStmt;
use GoParser\Ast\Stmt\IfStmt;
use GoParser\Ast\Stmt\IncDecStmt;
use GoParser\Ast\Stmt\LabeledStmt;
use GoParser\Ast\Stmt\ReturnStmt;
use GoParser\Ast\Stmt\ShortVarDecl;
use GoParser\Ast\Stmt\Stmt;
use GoParser\Ast\Stmt\SwitchStmt;
use GoParser\Ast\Stmt\VarDecl;
use GoParser\Ast\StmtList;
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
use GoPhp\GoType\FuncType;
use GoPhp\GoType\GoType;
use GoPhp\GoType\MapType;
use GoPhp\GoType\NamedType;
use GoPhp\GoType\PointerType;
use GoPhp\GoType\SliceType;
use GoPhp\GoValue\Array\ArrayBuilder;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\BuiltinFuncValue;
use GoPhp\GoValue\Float\UntypedFloatValue;
use GoPhp\GoValue\Func\FuncValue;
use GoPhp\GoValue\Func\Param;
use GoPhp\GoValue\Func\Params;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Int\BaseIntValue;
use GoPhp\GoValue\Int\Iota;
use GoPhp\GoValue\Int\UntypedIntValue;
use GoPhp\GoValue\Invocable;
use GoPhp\GoValue\Map\MapBuilder;
use GoPhp\GoValue\Map\MapLookupValue;
use GoPhp\GoValue\Sequence;
use GoPhp\GoValue\Slice\SliceBuilder;
use GoPhp\GoValue\Slice\SliceValue;
use GoPhp\GoValue\Sliceable;
use GoPhp\GoValue\StringValue;
use GoPhp\GoValue\TupleValue;
use GoPhp\GoValue\TypeValue;
use GoPhp\StmtValue\GotoValue;
use GoPhp\StmtValue\ReturnValue;
use GoPhp\StmtValue\SimpleValue;
use GoPhp\StmtValue\StmtValue;
use GoPhp\Stream\StdStreamProvider;
use GoPhp\Stream\StreamProvider;

final class Interpreter
{
    private State $state = State::DeclEvaluation;
    private Environment $env;
    private Iota $iota;
    private ?string $curPackage = null;
    private ?FuncValue $entryPoint = null;
    private bool $constDefinition = false;
    private JumpStack $jumpStack;
    private DeferStack $deferStack;
    private int $switchContext = 0;

    public function __construct(
        private readonly Ast $ast,
        private readonly array $argv = [],
        private readonly StreamProvider $streams = new StdStreamProvider(),
        private readonly EntryPointValidator $entryPointValidator = new MainEntryPoint(),
        ?BuiltinProvider $builtin = null,
    ) {
        if ($builtin === null) {
            $builtin = new StdBuiltinProvider($this->streams);
        }

        $this->iota = $builtin->iota();
        $this->jumpStack = new JumpStack();
        $this->deferStack = new DeferStack();
        $this->env = new Environment($builtin->env());
    }

    public static function fromString(
        string $src,
        array $argv = [],
        StreamProvider $streams = new StdStreamProvider(),
        EntryPointValidator $entryPointValidator = new MainEntryPoint(),
        ?BuiltinProvider $builtin = null,
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
                $this->callFunc(
                    fn (): GoValue => ($this->entryPoint)(...$this->argv)
                );
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
                $stmt instanceof FallthroughStmt => $this->evalFallthroughStmt($stmt),
                $stmt instanceof ContinueStmt => $this->evalContinueStmt($stmt),
                $stmt instanceof ExprStmt => $this->evalExprStmt($stmt),
                $stmt instanceof BlockStmt => $this->evalBlockStmt($stmt),
                $stmt instanceof IfStmt => $this->evalIfStmt($stmt),
                $stmt instanceof ForStmt => $this->evalForStmt($stmt),
                $stmt instanceof ExprSwitchStmt => $this->evalExprSwitchStmt($stmt),
                $stmt instanceof DeferStmt => $this->evalDeferStmt($stmt),
                $stmt instanceof IncDecStmt => $this->evalIncDecStmt($stmt),
                $stmt instanceof ReturnStmt => $this->evalReturnStmt($stmt),
                $stmt instanceof LabeledStmt => $this->evalLabeledStmt($stmt),
                $stmt instanceof GotoStmt => $this->evalGotoStmt($stmt),
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
        $this->constDefinition = true;
        $initExprs = [];

        foreach (self::wrapSpecs($decl->spec) as $j => $spec) {
            $this->iota->setValue($j);

            /** @var ConstSpec $spec */
            $type = null;
            if ($spec->type !== null) {
                $type = $this->resolveType($spec->type);
            }

            if ($type !== null && !$type instanceof NamedType) {
                throw DefinitionError::constantExpectsBasicType($type);
            }

            if (!empty($spec->initList->exprs)) {
                $initExprs = $spec->initList->exprs;
            }

            if (\count($initExprs) > \count($spec->identList->idents)) {
                throw new \Exception('extra init expr');
            }

            foreach ($spec->identList->idents as $i => $ident) {
                $value = isset($initExprs[$i]) ?
                    $this->tryEvalConstExpr($initExprs[$i]) :
                    null;

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

        $this->constDefinition = false;

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

            $values = [];
            $identsLen = \count($spec->identList->idents);

            if ($spec->initList === null) {
                if ($type === null) {
                    throw DefinitionError::uninitilisedVarWithNoType();
                }

                for ($i = 0; $i < $identsLen; ++$i) {
                    $values[] = $type->defaultValue();
                }
            } else {
                $values = $this->collectValuesFromExprList($spec->initList, $identsLen);
            }

            foreach ($spec->identList->idents as $i => $ident) {
                $this->defineVar($ident->name, $values[$i], $type);
            }
        }

        return SimpleValue::None;
    }

    private function evalFuncDeclStmt(FuncDecl $decl): SimpleValue
    {
        [$params, $returns] = $this->resolveParamsFromAstSignature($decl->signature);

        if ($decl->body === null) {
            throw new InternalError('not implemented');
        }

        $funcValue = new FuncValue(
            fn (Environment $env) => $this->evalBlockStmt($decl->body, $env),
            $params,
            $returns,
            $this->env,
            $this->streams,
        ); //fixme body null

        $this->env->defineFunc($decl->name->name, $funcValue);
        $this->checkEntryPoint($decl->name->name, $funcValue);

        return SimpleValue::None;
    }

    private function evalDeferStmt(DeferStmt $stmt): SimpleValue
    {
        if (!$stmt->expr instanceof CallExpr) {
            throw new InternalError('Call expression expected in defer statement');
        }

        $fn = $this->evalCallExprWithoutCall($stmt->expr);
        $this->deferStack->push($fn);

        return SimpleValue::None;
    }

    private function evalCallExprWithoutCall(CallExpr $expr): callable
    {
        $func = $this->evalExpr($expr->expr);

        if (!$func instanceof Invocable) {
            throw OperationError::nonFunctionCall($func);
        }

        $argv = [];
        $exprLen = \count($expr->args->exprs);

        // fixme move this to sep fn
        if (
            $func instanceof BuiltinFuncValue
            && $exprLen > 0
            && $expr->args->exprs[0] instanceof AstType
        ) {
            $argv[] = new TypeValue($this->resolveType($expr->args->exprs[0]));
        }

        for ($i = \count($argv); $i < $exprLen; ++$i) {
            $argv[] = $this->evalExpr($expr->args->exprs[$i])->copy();
        }

        // fixme assert_argc refactor
        if ($func instanceof FuncValue) {
            // we have to do it here to validate argc before the slice unpacking
            assert_argc(
                $argv,
                $func->signature->arity,
                $func->signature->variadic,
                $func->signature->params
            );
        }

        if ($expr->ellipsis !== null) {
            $slice = \array_pop($argv);
            assert_arg_value($slice, SliceValue::class, SliceValue::NAME, $exprLen - 1);

            /** @var SliceValue $slice */
            $argv = [...$argv, ...$slice->unwrap()];
        }

        return static fn () => $func(...$argv);
    }

    private function evalCallExpr(CallExpr $expr): GoValue
    {
        $fn = $this->evalCallExprWithoutCall($expr);

        return $this->callFunc($fn);
    }

    private function callFunc(callable $fn): GoValue
    {
        $this->jumpStack->push(new JumpHandler());
        $this->deferStack->newContext();

        $value = $fn();

        foreach ($this->deferStack->pop() as $defFn) {
            $this->callFunc($defFn);
        }

        $this->jumpStack->pop();

        return $value;
    }

    private function evalIndexExpr(IndexExpr $expr): GoValue
    {
        $sequence = $this->evalExpr($expr->expr);

        if (!$sequence instanceof Sequence) {
            throw OperationError::cannotIndex($sequence->type());
        }

        $index = $this->evalExpr($expr->index);

        return $sequence->get($index);
    }

    private function evalSliceExpr(SliceExpr $expr): StringValue|SliceValue
    {
        $sequence = $this->evalExpr($expr->expr);

        if (!$sequence instanceof Sliceable) {
            throw OperationError::cannotSlice($sequence->type());
        }

        $low = $this->getSliceExprIndex($expr->low);
        $high = $this->getSliceExprIndex($expr->high);
        $max = $expr instanceof FullSliceExpr ?
            $this->getSliceExprIndex($expr->max) :
            null;

        return $sequence->slice($low, $high, $max);
    }

    private function getSliceExprIndex(?Expr $expr): ?int
    {
        if ($expr === null) {
            return null;
        }

        $index = $this->evalExpr($expr);
        assert_index_value($index, BaseIntValue::class, 'slice'); //fixme name

        return $index->unwrap();
    }

    private function evalEmptyStmt(EmptyStmt $stmt): SimpleValue
    {
        return SimpleValue::None;
    }

    private function evalBreakStmt(BreakStmt $stmt): SimpleValue
    {
        return SimpleValue::Break;
    }

    private function evalFallthroughStmt(FallthroughStmt $stmt): SimpleValue
    {
        if ($this->switchContext <= 0) {
            throw new DefinitionError('fallthrough outside switch');
        }

        return SimpleValue::Fallthrough;
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
        return $this->evalStmtList($blockStmt->stmtList, $env);
    }

    private function evalStmtList(StmtList $stmtList, ?Environment $env = null): StmtValue
    {
        $jump = $this->jumpStack->peek();
        $jump->setContext($stmtList);

        return $this->evalWithEnvWrap($env, function () use ($stmtList, $jump): StmtValue {
            $stmtVal = SimpleValue::None;
            $len = \count($stmtList->stmts);
            $gotoIndex = 0;

            for ($i = 0; $i < $len; ++$i) {
                $stmt = $stmtList->stmts[$i];

                // fixme refactor
                if ($jump->isSeeking()) {
                    $stmt = $jump->tryFindLabel($stmt, $gotoIndex > $i);

                    if ($stmt === null) {
                        continue;
                    }
                }

                $stmtVal = $this->evalStmt($stmt);

                if ($stmtVal instanceof GotoValue) {
                    $jump->startSeeking($stmtVal->label);

                    if ($jump->isSameContext($stmtList)) {
                        /**
                         * @psalm-suppress LoopInvalidation
                         */
                        [$i, $gotoIndex] = [-1, $i];
                        continue;
                    }

                    return $stmtVal;
                }

                if (
                    $stmtVal instanceof ReturnValue
                    || $stmtVal === SimpleValue::Continue
                    || $stmtVal === SimpleValue::Break
                ) {
                    break;
                }

                if ($stmtVal === SimpleValue::Fallthrough && $i + 1 < $len) {
                    throw new DefinitionError('fallthrough can only appear as the last statement');
                }
            }

            if ($stmtVal instanceof GotoValue) {
                throw DefinitionError::undefinedLabel($jump->getLabel());
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

    private function evalLabeledStmt(LabeledStmt $stmt): StmtValue
    {
        $this->jumpStack->peek()->addLabel($stmt);

        return $this->evalStmt($stmt->stmt);
    }

    private function evalGotoStmt(GotoStmt $stmt): GotoValue
    {
        return new GotoValue($stmt->label->name);
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
                // for range {}
                case $stmt->iteration instanceof RangeClause:
                    return $this->evalForRangeStmt($stmt);
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
                        default => throw new InternalError('Unknown for loop condition'),
                    };

                    $post = $stmt->iteration->post ?? null;
                    break;
                default:
                    throw new InternalError('Unknown for loop structure');
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
                    case $stmtValue instanceof ReturnValue
                        || $stmtValue instanceof GotoValue:
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

    private function evalExprSwitchStmt(ExprSwitchStmt $stmt): StmtValue
    {
        return $this->evalWithEnvWrap(null, function () use ($stmt): StmtValue {
            ++$this->switchContext;

            if ($stmt->init !== null) {
                $this->evalStmt($stmt->init);
            }

            $condition = $stmt->condition === null ?
                BoolValue::true() :
                $this->evalExpr($stmt->condition);

            $stmtValue = SimpleValue::None;
            $defaultCaseIndex = null;

            foreach ($stmt->caseClauses as $i => $caseClause) {
                if ($caseClause->case instanceof DefaultCase) {
                    if ($defaultCaseIndex !== null) {
                        throw new DefinitionError('Multiple default cases in switch');
                    }

                    $defaultCaseIndex = $i;
                    continue;
                }

                foreach ($caseClause->case->exprList->exprs as $expr) {
                    $caseCondition = $this->evalExpr($expr);
                    $equal = $caseCondition->operateOn(Operator::EqEq, $condition);

                    if ($equal instanceof BoolValue && $equal->isTrue()) {
                        // todo check for fall last
                        // todo and not the last case
                        $stmtValue = $this->evalExprCaseClause($caseClause, $i, $stmt);

                        goto end_switch;
                    }
                }
            }

            if ($defaultCaseIndex !== null) {
                $stmtValue = $this->evalExprCaseClause($stmt->caseClauses[$defaultCaseIndex], $defaultCaseIndex, $stmt);
            }

            end_switch:

            --$this->switchContext;

            return $stmtValue;
        });
    }

    private function evalExprCaseClause(ExprCaseClause $caseClause, int $caseIndex, ExprSwitchStmt $stmt): StmtValue
    {
        $stmtValue = $this->evalStmtList($caseClause->stmtList);

        if ($stmtValue === SimpleValue::Fallthrough) {
            $stmtValue = $this->evalSwitchWithFallthrough($stmt, $caseIndex + 1);
        }

        if ($stmtValue === SimpleValue::Break) {
            $stmtValue = SimpleValue::None;
        }

        return $stmtValue;
    }

    private function evalSwitchWithFallthrough(SwitchStmt $stmt, int $fromCase): StmtValue
    {
        $stmtValue = SimpleValue::None;

        for (
            $i = $fromCase,
            $caseClausesLen = \count($stmt->caseClauses);
            $i < $caseClausesLen;
            $i++
        ) {
            $stmtValue = $this->evalStmtList($stmt->caseClauses[$i]->stmtList);

            if ($stmtValue === SimpleValue::Fallthrough) {
                $stmtValue = SimpleValue::None;
                continue;
            }

            break;
        }

        return $stmtValue;
    }

    private function evalForRangeStmt(ForStmt $stmt): StmtValue
    {
        /** @var RangeClause $iteration */
        $iteration = $stmt->iteration;
        $range = $this->evalExpr($iteration->expr);

        if (!$range instanceof Sequence) {
            throw new ProgramError(\sprintf('cannot range over %s', $range->type()->name()));
        }

        [$define, $iterVars] = match (true) {
            $iteration->list instanceof ExprList => [false, $iteration->list->exprs],
            $iteration->list instanceof IdentList => [true, $iteration->list->idents],
            default => [false, []],
        };

        [$keyVar, $valVar] = match (\count($iterVars)) {
            0 => [null, null],
            1 => [$iterVars[0], null],
            2 => $iterVars,
            default => throw new ProgramError('range clause permits at most two iteration variables'),
        };

        foreach ($range->iter() as $key => $value) {
            /**
             * @var GoValue $key
             * @var GoValue $value
             */
            if ($define) {
                if ($keyVar !== null) {
                    $this->defineVar($keyVar->name, $key);
                }

                if ($valVar !== null) {
                    $this->defineVar($valVar->name, $value);
                }

                $define = false;
            }

            if ($keyVar !== null) {
                $this->evalLhsExpr($keyVar)->mutate(Operator::Eq, $key->copy());
            }

            if ($valVar !== null) {
                $this->evalLhsExpr($valVar)->mutate(Operator::Eq, $value->copy());
            }

            $stmtValue = $this->evalBlockStmt($stmt->body);

            // fixme unify
            switch (true) {
                case $stmtValue === SimpleValue::None:
                    break;
                case $stmtValue === SimpleValue::Continue:
                    continue 2;
                case $stmtValue === SimpleValue::Break:
                    return SimpleValue::None;
                case $stmtValue instanceof ReturnValue
                    || $stmtValue instanceof GotoValue:
                    return $stmtValue;
                default:
                    throw new InternalError('Unknown statement value');
            }
        }

        return SimpleValue::None;
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
        $op = Operator::fromAst($stmt->op);

        if (!$op->isAssignment()) {
            throw OperationError::expectedAssignmentOperator($op);
        }

        $lhs = [];
        foreach ($stmt->lhs->exprs as $expr) {
            $lhs[] = $this->evalLhsExpr($expr);
        }

        $lhsLen = \count($lhs);
        $rhs = $this->collectValuesFromExprList($stmt->rhs, $lhsLen);

        for ($i = 0; $i < $lhsLen; ++$i) {
            $lhs[$i]->mutate($op, $rhs[$i]);
        }

        return SimpleValue::None;
    }

    private function evalShortVarDeclStmt(ShortVarDecl $stmt): SimpleValue
    {
        $len = \count($stmt->identList->idents);
        $values = $this->collectValuesFromExprList($stmt->exprList, $len);

        foreach ($stmt->identList->idents as $i => $ident) {
            $this->defineVar($ident->name, $values[$i]);
        }

        return SimpleValue::None;
    }

    private function collectValuesFromExprList(ExprList $exprList, int $expectedLen): array
    {
        $value = $this->evalExpr($exprList->exprs[0]);
        $exprLen = \count($exprList->exprs);

        if ($value instanceof TupleValue) {
            if ($exprLen !== 1) {
                throw ValueError::multipleValueInSingleContext();
            }

            if ($value->len !== $expectedLen) {
                throw DefinitionError::assignmentMismatch($expectedLen, $value->len);
            }

            return $value->values;
        }

        if (
            $expectedLen === 2
            && $exprLen === 1
            && $value instanceof MapLookupValue
        ) {
            return [$value->value, $value->ok];
        }

        if ($expectedLen !== $exprLen) {
            throw DefinitionError::assignmentMismatch($expectedLen, $exprLen);
        }

        $values = [$value];

        for ($i = 1; $i < $expectedLen; ++$i) {
            $value = $this->evalExpr($exprList->exprs[$i]);

            if ($value instanceof TupleValue) {
                throw ValueError::multipleValueInSingleContext();
            }

            $values[] = $value;
        }

        return $values;
    }

    private function evalExpr(Expr $expr): GoValue
    {
        $value = $this->tryEvalConstExpr($expr);

        return match (true) {
            // literals
            $value !== null => $value,
            $expr instanceof CallExpr => $this->evalCallExpr($expr),
            $expr instanceof IndexExpr => $this->evalIndexExpr($expr),
            $expr instanceof SliceExpr => $this->evalSliceExpr($expr),
            $expr instanceof CompositeLit => $this->evalCompositeLit($expr),
//            $expr instanceof AstType => $this->evalTypeConversion($expr),
            default => dd('eval expr', $expr), // fixme debug
        };
    }

    private function tryEvalConstExpr(Expr $expr): ?GoValue
    {
        return match (true) {
            // literals
            $expr instanceof RuneLit => $this->evalRuneLit($expr),
            $expr instanceof StringLit => $this->evalStringLit($expr),
//            $expr instanceof RawStringLit => $this->evalStringLit($expr),
            $expr instanceof IntLit => $this->evalIntLit($expr),
            $expr instanceof FloatLit => $this->evalFloatLit($expr),
            $expr instanceof UnaryExpr => $this->evalUnaryExpr($expr),
            $expr instanceof BinaryExpr => $this->evalBinaryExpr($expr),
            $expr instanceof GroupExpr => $this->evalGroupExpr($expr),
            $expr instanceof Ident => $this->evalIdent($expr),
            default => null,
        };
    }

//    private function evalTypeConversion(AstType $expr): TypeValue
//    {
//        // fixme add []byte, []rune, errors
//    }

    private function evalConstExpr(Expr $expr): GoValue
    {
        return $this->tryEvalConstExpr($expr) ?? throw new \Exception('cannot eval const expr');
    }

    /**
     * Evaluate left-hand-side (lhs) expression of an assignment statement.
     */
    private function evalLhsExpr(Expr $expr): GoValue
    {
        return match (true) {
            $expr instanceof Ident => $this->evalIdent($expr),
            $expr instanceof IndexExpr => $this->evalIndexExpr($expr),
            $expr instanceof UnaryExpr => $this->evalPointerUnaryExpr($expr),
            default => dd($expr), // fixme debug
        };
    }

    private function evalCompositeLit(CompositeLit $lit): GoValue //fixme arrayvalye, slice, map, struct etc...
    {
        $type = $this->resolveType($lit->type, true);

        switch (true) {
            case $type instanceof ArrayType:
                $builder = ArrayBuilder::fromType($type);
                foreach ($lit->elementList->elements ?? [] as $element) {
                    $builder->push($this->evalExpr($element->element));
                }
                return $builder->build();
            case $type instanceof SliceType:
                $builder = SliceBuilder::fromType($type);
                foreach ($lit->elementList->elements ?? [] as $element) {
                    $builder->push($this->evalExpr($element->element));
                }
                return $builder->build();
            case $type instanceof MapType:
                $builder = MapBuilder::fromType($type);
                foreach ($lit->elementList->elements ?? [] as $element) {
                    $builder->set(
                        $this->evalExpr($element->element),
                        $this->evalExpr($element->key ?? throw new InternalError('Expected element key')),
                    );
                }
                return $builder->build();
        }

        throw new InternalError('Unknown composite literal');
    }

    private function evalRuneLit(RuneLit $lit): UntypedIntValue
    {
        return UntypedIntValue::fromRune(\trim($lit->rune, '\''));
    }

    private function evalStringLit(StringLit $lit): StringValue
    {
        return new StringValue(\trim($lit->str, '"'));
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
        $left = $this->evalExpr($expr->lExpr);
        $op = Operator::fromAst($expr->op);

        // short circuit evaluation
        if (
            $left instanceof BoolValue
            && (
                ($op === Operator::LogicAnd && $left->isFalse())
                || ($op === Operator::LogicOr && $left->isTrue())
            )
        ) {
            return $left;
        }

        return $left->operateOn(
            $op,
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
        $value = $this->env->get($ident->name)->unwrap();

        if ($value === $this->iota && !$this->constDefinition) {
            throw new \Exception('cannot use iota outside constant declaration');
        }

        return $value;
    }

    private function evalPointerUnaryExpr(UnaryExpr $expr): GoValue
    {
        $value = $this->evalUnaryExpr($expr);

        if ($expr->op->value !== Operator::Mul->value) {
            throw OperationError::cannotAssign(\sprintf('%s(%s)', $expr->op->value, $value->toString())); //fixme move to err
        }

        return $value;
    }

    private static function isTrue(GoValue $value): bool
    {
        if (!$value instanceof BoolValue) {
            throw TypeError::valueOfWrongType($value, NamedType::Bool);
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
     * @psalm-suppress InvalidReturnStatement
     */
    private static function wrapSpecs(Spec $spec): iterable
    {
        return $spec instanceof GroupSpec ?
            yield from $spec->specs :
            yield $spec;
    }

    private function resolveType(AstType $type, bool $composite = false): GoType
    {
        return match (true) {
            $type instanceof SingleTypeName => $this->env->getType($type->name->name)->getType(),
            $type instanceof QualifiedTypeName => $this->env->getType(self::resolveQualifiedTypeName($type))->getType(),
            $type instanceof AstFuncType => $this->resolveTypeFromAstSignature($type->signature),
            $type instanceof AstArrayType => $this->resolveArrayType($type, $composite),
            $type instanceof AstSliceType => $this->resolveSliceType($type, $composite),
            $type instanceof AstMapType => $this->resolveMapType($type, $composite),
            $type instanceof AstPointerType => $this->resolvePointerType($type, $composite),
            default => dd('unresolved type', $type), // fixme debug
        };
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
                throw TypeError::valueOfWrongType($len, NamedType::Int);
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

    private function resolveMapType(AstMapType $mapType, bool $composite): MapType
    {
        return new MapType(
            $this->resolveType($mapType->keyType, $composite),
            $this->resolveType($mapType->elemType, $composite),
        );
    }

    private function resolvePointerType(AstPointerType $pointerType, bool $composite): PointerType
    {
        return new PointerType(
            $this->resolveType($pointerType->type, $composite),
        );
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
        return \sprintf('%s.%s', $typeName->packageName->name, $typeName->typeName->name->name);
    }

    private function defineVar(string $name, GoValue $value, ?GoType $type = null): void
    {
        $this->env->defineVar(
            $name,
            $value->copy(),
            ($type ?? $value->type())->reify(),
        );
    }
}
