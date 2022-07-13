<?php

declare(strict_types=1);

namespace GoPhp;

use GoParser\Ast\AliasDecl;
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
use GoParser\Ast\Expr\SelectorExpr;
use GoParser\Ast\Expr\SingleTypeName;
use GoParser\Ast\Expr\SliceExpr;
use GoParser\Ast\Expr\SliceType as AstSliceType;
use GoParser\Ast\Expr\StringLit;
use GoParser\Ast\Expr\Type as AstType;
use GoParser\Ast\Expr\UnaryExpr;
use GoParser\Ast\ExprCaseClause;
use GoParser\Ast\ExprList;
use GoParser\Ast\File as Ast;
use GoParser\Ast\ForClause;
use GoParser\Ast\GroupSpec;
use GoParser\Ast\IdentList;
use GoParser\Ast\ImportSpec;
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
use GoParser\Ast\Stmt\GoStmt;
use GoParser\Ast\Stmt\GotoStmt;
use GoParser\Ast\Stmt\IfStmt;
use GoParser\Ast\Stmt\ImportDecl;
use GoParser\Ast\Stmt\IncDecStmt;
use GoParser\Ast\Stmt\LabeledStmt;
use GoParser\Ast\Stmt\ReturnStmt;
use GoParser\Ast\Stmt\ShortVarDecl;
use GoParser\Ast\Stmt\Stmt;
use GoParser\Ast\Stmt\SwitchStmt;
use GoParser\Ast\Stmt\TypeDecl;
use GoParser\Ast\Stmt\VarDecl;
use GoParser\Ast\StmtList;
use GoParser\Ast\TypeDef;
use GoParser\Ast\TypeSpec;
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
use GoPhp\GoType\WrappedType;
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
use GoPhp\StmtJump\BreakJump;
use GoPhp\StmtJump\ContinueJump;
use GoPhp\StmtJump\FallthroughJump;
use GoPhp\StmtJump\GotoJump;
use GoPhp\StmtJump\None;
use GoPhp\StmtJump\ReturnJump;
use GoPhp\StmtJump\StmtJump;
use GoPhp\Stream\StdStreamProvider;
use GoPhp\Stream\StreamProvider;

final class Interpreter
{
    private Ast $ast;
    private Environment $env;
    private Iota $iota;
    private JumpStack $jumpStack;
    private DeferStack $deferStack;
    private string $currentPackage;
    private ?FuncValue $entryPoint = null;
    private bool $constDefinition = false;
    private int $switchContext = 0;

    public function __construct(
        Ast $ast,
        private readonly array $argv = [],
        private readonly StreamProvider $streams = new StdStreamProvider(),
        private readonly EntryPointValidator $entryPointValidator = new MainEntryPoint(),
        ?BuiltinProvider $builtin = null,
        private readonly string $gopath = '',
    ) {
        if ($builtin === null) {
            $builtin = new StdBuiltinProvider($this->streams);
        }

        $this->iota = $builtin->iota();
        $this->jumpStack = new JumpStack();
        $this->deferStack = new DeferStack();
        $this->env = new Environment($builtin->env());
        $this->setAst($ast);
    }

    public static function fromString(
        string $source,
        array $argv = [],
        StreamProvider $streams = new StdStreamProvider(),
        EntryPointValidator $entryPointValidator = new MainEntryPoint(),
        ?BuiltinProvider $builtin = null,
        string $gopath = '',
    ): self {
        $ast = self::parseSourceToAst($source);

        return new self($ast, $argv, $streams, $entryPointValidator, $builtin, $gopath);
    }

    public function run(): ExecCode
    {
        try {
            $this->evalDeclsInOrder();

            if ($this->entryPoint === null) {
                throw new ProgramError('no entry point');
            }

            $this->callFunc(
                fn (): GoValue => ($this->entryPoint)(...$this->argv)
            );
        } catch (\Throwable $throwable) {
            $this->streams->stderr()->writeln($throwable->getMessage());

            return ExecCode::Failure;
        }

        return ExecCode::Success;
    }

    private function evalDeclsInOrder(): void
    {
        foreach ($this->ast->imports as $import) {
            $this->evalImportDeclStmt($import);
        }

        $types = [];
        $vars = [];
        $funcs = [];
        $consts = [];

        foreach ($this->ast->decls as $i => $decl) {
            match (true) {
                $decl instanceof ConstDecl => $consts[] = $i,
                $decl instanceof VarDecl => $vars[] = $i,
                $decl instanceof TypeDecl => $types[] = $i,
                $decl instanceof FuncDecl => $funcs[] = $i,
                default => throw new ProgramError('Non-declaration on a top-level'),
            };
        }

        foreach ($types as $i) {
            $this->evalTypeDeclStmt($this->ast->decls[$i]);
        }

        foreach ($consts as $i) {
            $this->evalConstDeclStmt($this->ast->decls[$i]);
        }

        foreach ($vars as $i) {
            $this->evalVarDeclStmt($this->ast->decls[$i]);
        }

        foreach ($funcs as $i) {
            $this->evalFuncDeclStmt($this->ast->decls[$i]);
        }
    }

    private function evalImportDeclStmt(ImportDecl $decl): void
    {
        $mainAst = $this->ast;

        foreach (self::wrapSpecs($decl->spec) as $spec) {
            /** @var ImportSpec $spec */
            $path = \trim(\trim($spec->path->str, '"'), '"');
            $path = $this->resolveImportPath($path);
            $source = \file_get_contents($path);
            $ast = self::parseSourceToAst($source);

            $this->setAst($ast);
            $this->evalDeclsInOrder();
        }

        $this->setAst($mainAst);
    }

    private function setAst(Ast $ast): void
    {
        $this->ast = $ast;
        $this->currentPackage = $ast->package->identifier->name;
    }

    private function resolveImportPath(string $path): string
    {
        // fixme add _ . support
        // fixme add go.mod support
        $path = \sprintf('%s%s.go', $this->gopath, $path);

        return $path;
    }

    private static function parseSourceToAst(string $source): Ast
    {
        // fixme add onerror
        $parser = new Parser($source);
        /** @var Ast $ast */
        $ast = $parser->parse();

        if ($parser->hasErrors()) {
            // fixme handle errs
            dump('has parse errs');
            foreach ($parser->getErrors() as $error) {
                dump((string)$error);
            }
            die; //fixme
        }

        return $ast;
    }

    private function evalStmt(Stmt $stmt): StmtJump
    {
        return match (true) {
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
            $stmt instanceof GoStmt => $this->evalGoStmt($stmt),
            $stmt instanceof AssignmentStmt => $this->evalAssignmentStmt($stmt),
            $stmt instanceof ShortVarDecl => $this->evalShortVarDeclStmt($stmt),
            $stmt instanceof ConstDecl => $this->evalConstDeclStmt($stmt),
            $stmt instanceof VarDecl => $this->evalVarDeclStmt($stmt),
            $stmt instanceof TypeDecl => $this->evalTypeDeclStmt($stmt),
            $stmt instanceof FuncDecl => throw new ProgramError('Function declaration in a function scope'),
            default => throw new ProgramError(\sprintf('Unknown statement %s', $stmt::class)),
        };
    }

    private function evalConstDeclStmt(ConstDecl $decl): None
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
                    $this->currentPackage,
                    $value->copy(),
                    $type?->reify() ?? $value->type(),
                );
            }
        }

        $this->constDefinition = false;

        return None::get();
    }

    private function evalVarDeclStmt(VarDecl $decl): None
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

        return None::get();
    }

    private function evalTypeDeclStmt(TypeDecl $decl): None
    {
        foreach (self::wrapSpecs($decl->spec) as $spec) {
            /** @var TypeSpec $spec */
            match (true) {
                $spec->value instanceof AliasDecl => $this->evalAliasDeclStmt($spec->value),
                $spec->value instanceof TypeDef => $this->evalTypeDefStmt($spec->value),
            };
        }

        return None::get();
    }

    private function evalAliasDeclStmt(AliasDecl $decl): void
    {
        $alias = $decl->ident->name;
        $typeValue = new TypeValue($this->resolveType($decl->type));

        $this->env->defineTypeAlias($alias, $this->currentPackage, $typeValue);
    }

    private function evalTypeDefStmt(TypeDef $stmt): void
    {
        $name = $stmt->ident->name;
        $type = $this->resolveType($stmt->type);

        $typeValue = new TypeValue(new WrappedType($name, $type));

        // fixme add generics support

        $this->env->defineType($name, $this->currentPackage, $typeValue);
    }

    private function evalFuncDeclStmt(FuncDecl $decl): void
    {
        [$params, $returns] = $this->resolveParamsFromAstSignature($decl->signature);

        if ($decl->body === null) {
            throw new InternalError('not implemented');
        }

        $funcValue = new FuncValue(
            function (Environment $env, string $withPackage) use ($decl): StmtJump {
                [$this->currentPackage, $withPackage] = [$withPackage, $this->currentPackage];
                $jump = $this->evalBlockStmt($decl->body, $env);
                $this->currentPackage = $withPackage;

                return $jump;
            },
            $params,
            $returns,
            $this->env,
            $this->streams,
            $this->currentPackage,
        ); //fixme body null

        $this->env->defineFunc($decl->name->name, $this->currentPackage, $funcValue);
        $this->checkEntryPoint($decl->name->name, $funcValue);
    }

    private function evalDeferStmt(DeferStmt $stmt): None
    {
        if (!$stmt->expr instanceof CallExpr) {
            throw new InternalError('Call expression expected in defer statement');
        }

        $fn = $this->evalCallExprWithoutCall($stmt->expr);
        $this->deferStack->push($fn);

        return None::get();
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

    private function evalEmptyStmt(EmptyStmt $stmt): None
    {
        return None::get();
    }

    private function evalBreakStmt(BreakStmt $stmt): BreakJump
    {
        return new BreakJump();
    }

    private function evalFallthroughStmt(FallthroughStmt $stmt): FallthroughJump
    {
        if ($this->switchContext <= 0) {
            throw new DefinitionError('fallthrough outside switch');
        }

        return new FallthroughJump();
    }

    private function evalContinueStmt(ContinueStmt $stmt): ContinueJump
    {
        return new ContinueJump();
    }

    private function evalExprStmt(ExprStmt $stmt): None
    {
        $this->evalExpr($stmt->expr);

        return None::get();
    }

    private function evalBlockStmt(BlockStmt $blockStmt, ?Environment $env = null): StmtJump
    {
        return $this->evalStmtList($blockStmt->stmtList, $env);
    }

    private function evalStmtList(StmtList $stmtList, ?Environment $env = null): StmtJump
    {
        $jump = $this->jumpStack->peek();
        $jump->setContext($stmtList);

        return $this->evalWithEnvWrap($env, function () use ($stmtList, $jump): StmtJump {
            $stmtJump = None::get();
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

                $stmtJump = $this->evalStmt($stmt);

                if ($stmtJump instanceof GotoJump) {
                    $jump->startSeeking($stmtJump->label);

                    if ($jump->isSameContext($stmtList)) {
                        /**
                         * @psalm-suppress LoopInvalidation
                         */
                        [$i, $gotoIndex] = [-1, $i];
                        continue;
                    }

                    return $stmtJump;
                }

                if (
                    $stmtJump instanceof ReturnJump
                    || $stmtJump instanceof ContinueJump
                    || $stmtJump instanceof BreakJump
                ) {
                    break;
                }

                if ($stmtJump instanceof FallthroughJump && $i + 1 < $len) {
                    throw new DefinitionError('fallthrough can only appear as the last statement');
                }
            }

            if ($stmtJump instanceof GotoJump) {
                throw DefinitionError::undefinedLabel($jump->getLabel());
            }

            return $stmtJump;
        });
    }

    /**
     * @var callable(): None $code
     */
    private function evalWithEnvWrap(?Environment $env, callable $code): StmtJump
    {
        $prevEnv = $this->env;
        $this->env = $env ?? new Environment($this->env);
        $stmtJump = $code();
        $this->env = $prevEnv;

        return $stmtJump;
    }

    private function evalReturnStmt(ReturnStmt $stmt): ReturnJump
    {
        if (empty($stmt->exprList->exprs)) {
            return ReturnJump::fromVoid();
        }

        $values = [];

        foreach ($stmt->exprList->exprs as $expr) {
            $value = $this->evalExpr($expr);

            if ($value instanceof TupleValue) {
                if (!empty($values)) {
                    throw ValueError::multipleValueInSingleContext();
                }
                return ReturnJump::fromTuple($value);
            }

            $values[] = $value;
        }

        if (\count($values) === 1) {
            return ReturnJump::fromSingle($values[0]);
        }

        return ReturnJump::fromMultiple($values);
    }

    private function evalLabeledStmt(LabeledStmt $stmt): StmtJump
    {
        $this->jumpStack->peek()->addLabel($stmt);

        return $this->evalStmt($stmt->stmt);
    }

    private function evalGotoStmt(GotoStmt $stmt): GotoJump
    {
        return new GotoJump($stmt->label->name);
    }

    private function evalGoStmt(GoStmt $stmt): None
    {
        $this->evalCallExpr($stmt->expr);

        return None::get();
    }

    private function evalIfStmt(IfStmt $stmt): StmtJump
    {
        return $this->evalWithEnvWrap(null, function () use ($stmt): StmtJump {
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

            return None::get();
        });
    }

    private function evalForStmt(ForStmt $stmt): StmtJump
    {
        return $this->evalWithEnvWrap(null, function () use ($stmt): StmtJump {
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
                $stmtJump = $this->evalBlockStmt($stmt->body);

                switch (true) {
                    case $stmtJump instanceof None:
                        break;
                    case $stmtJump instanceof ContinueJump:
                        if ($post !== null) {
                            $this->evalStmt($post);
                        }
                        continue 2;
                    case $stmtJump instanceof BreakJump:
                        return None::get();
                    case $stmtJump instanceof ReturnJump
                        || $stmtJump instanceof GotoJump:
                        return $stmtJump;
                    default:
                        throw new InternalError('Unknown statement value');
                }

                if ($post !== null) {
                    $this->evalStmt($post);
                }
            }

            return None::get();
        });
    }

    private function evalExprSwitchStmt(ExprSwitchStmt $stmt): StmtJump
    {
        return $this->evalWithEnvWrap(null, function () use ($stmt): StmtJump {
            ++$this->switchContext;

            if ($stmt->init !== null) {
                $this->evalStmt($stmt->init);
            }

            $condition = $stmt->condition === null ?
                BoolValue::true() :
                $this->evalExpr($stmt->condition);

            $stmtJump = None::get();
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
                        $stmtJump = $this->evalExprCaseClause($caseClause, $i, $stmt);

                        goto end_switch;
                    }
                }
            }

            if ($defaultCaseIndex !== null) {
                $stmtJump = $this->evalExprCaseClause($stmt->caseClauses[$defaultCaseIndex], $defaultCaseIndex, $stmt);
            }

            end_switch:

            --$this->switchContext;

            return $stmtJump;
        });
    }

    private function evalExprCaseClause(ExprCaseClause $caseClause, int $caseIndex, ExprSwitchStmt $stmt): StmtJump
    {
        $stmtJump = $this->evalStmtList($caseClause->stmtList);

        if ($stmtJump instanceof FallthroughJump) {
            $stmtJump = $this->evalSwitchWithFallthrough($stmt, $caseIndex + 1);
        }

        if ($stmtJump instanceof BreakJump) {
            $stmtJump = None::get();
        }

        return $stmtJump;
    }

    private function evalSwitchWithFallthrough(SwitchStmt $stmt, int $fromCase): StmtJump
    {
        $stmtJump = None::get();

        for (
            $i = $fromCase,
            $caseClausesLen = \count($stmt->caseClauses);
            $i < $caseClausesLen;
            $i++
        ) {
            $stmtJump = $this->evalStmtList($stmt->caseClauses[$i]->stmtList);

            if ($stmtJump instanceof FallthroughJump) {
                $stmtJump = None::get();
                continue;
            }

            break;
        }

        return $stmtJump;
    }

    private function evalForRangeStmt(ForStmt $stmt): StmtJump
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

            $stmtJump = $this->evalBlockStmt($stmt->body);

            // fixme unify
            switch (true) {
                case $stmtJump instanceof None:
                    break;
                case $stmtJump instanceof ContinueJump:
                    continue 2;
                case $stmtJump instanceof BreakJump:
                    return None::get();
                case $stmtJump instanceof ReturnJump
                    || $stmtJump instanceof GotoJump:
                    return $stmtJump;
                default:
                    throw new InternalError('Unknown statement value');
            }
        }

        return None::get();
    }

    private function evalIncDecStmt(IncDecStmt $stmt): None
    {
        $this
            ->evalExpr($stmt->lhs)
            ->mutate(
                Operator::fromAst($stmt->op),
                new UntypedIntValue(1)
            );

        return None::get();
    }

    private function evalAssignmentStmt(AssignmentStmt $stmt): None
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

        return None::get();
    }

    private function evalShortVarDeclStmt(ShortVarDecl $stmt): None
    {
        $len = \count($stmt->identList->idents);
        $values = $this->collectValuesFromExprList($stmt->exprList, $len);

        foreach ($stmt->identList->idents as $i => $ident) {
            $this->defineVar($ident->name, $values[$i]);
        }

        return None::get();
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
            $expr instanceof SelectorExpr => $this->evalSelectorExpr($expr),
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

    private function evalSelectorExpr(SelectorExpr $expr): GoValue
    {
        $namespace = $this->currentPackage;

        if ($expr->expr instanceof Ident) {
            $goValue = $this->env->tryGet($expr->expr->name, $namespace);

            if ($goValue === null) {
                $namespace = $expr->expr->name;

                return $this->env->get($expr->selector->name, $namespace)->unwrap();
            }
        }

        // fixme
        dump('not supported');
        exit;
    }

    private function evalIdent(Ident $ident): GoValue
    {
        $value = $this->env->get($ident->name, $this->currentPackage)->unwrap();

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
            $this->entryPointValidator->isEntryPackage($this->currentPackage)
            && !isset($this->entryPoint)
            && $this->entryPointValidator->validate($name, $funcValue->signature)
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
            $type instanceof SingleTypeName => $this->env->getType($type->name->name, $this->currentPackage)->getType(),
            $type instanceof QualifiedTypeName => $this->env->getType(self::resolveQualifiedTypeName($type), $this->currentPackage)->getType(),
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
            $this->currentPackage,
            $value->copy(),
            ($type ?? $value->type())->reify(),
        );
    }
}
