<?php

declare(strict_types=1);

namespace GoPhp;

use GoParser\Ast\AliasDecl;
use GoParser\Ast\DefaultCase;
use GoParser\Ast\Expr\ArrayType as AstArrayType;
use GoParser\Ast\Expr\BinaryExpr;
use GoParser\Ast\Expr\CallExpr;
use GoParser\Ast\Expr\CompositeLit;
use GoParser\Ast\Expr\Expr;
use GoParser\Ast\Expr\FloatLit;
use GoParser\Ast\Expr\FullSliceExpr;
use GoParser\Ast\Expr\FuncLit;
use GoParser\Ast\Expr\FuncType as AstFuncType;
use GoParser\Ast\Expr\GroupExpr;
use GoParser\Ast\Expr\Ident;
use GoParser\Ast\Expr\ImagLit;
use GoParser\Ast\Expr\IndexExpr;
use GoParser\Ast\Expr\IntLit;
use GoParser\Ast\Expr\MapType as AstMapType;
use GoParser\Ast\Expr\PointerType as AstPointerType;
use GoParser\Ast\Expr\QualifiedTypeName;
use GoParser\Ast\Expr\RawStringLit;
use GoParser\Ast\Expr\RuneLit;
use GoParser\Ast\Expr\SelectorExpr;
use GoParser\Ast\Expr\SimpleSliceExpr;
use GoParser\Ast\Expr\SingleTypeName;
use GoParser\Ast\Expr\SliceType as AstSliceType;
use GoParser\Ast\Expr\StringLit;
use GoParser\Ast\Expr\StructType as AstStructType;
use GoParser\Ast\Expr\InterfaceType as AstInterfaceType;
use GoParser\Ast\Expr\Type as AstType;
use GoParser\Ast\Expr\TypeTerm;
use GoParser\Ast\Expr\UnaryExpr;
use GoParser\Ast\ExprCaseClause;
use GoParser\Ast\ExprList;
use GoParser\Ast\File as Ast;
use GoParser\Ast\ForClause;
use GoParser\Ast\IdentList;
use GoParser\Ast\MethodElem;
use GoParser\Ast\Params as AstParams;
use GoParser\Ast\RangeClause;
use GoParser\Ast\Signature as AstSignature;
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
use GoParser\Ast\Stmt\MethodDecl;
use GoParser\Ast\Stmt\ReturnStmt;
use GoParser\Ast\Stmt\ShortVarDecl;
use GoParser\Ast\Stmt\Stmt;
use GoParser\Ast\Stmt\TypeDecl;
use GoParser\Ast\Stmt\TypeSwitchStmt;
use GoParser\Ast\Stmt\VarDecl;
use GoParser\Ast\StmtList;
use GoParser\Ast\TypeDef;
use GoParser\Lexer\Token;
use GoParser\Parser;
use GoPhp\Builtin\BuiltinProvider;
use GoPhp\Builtin\Iota;
use GoPhp\Builtin\StdBuiltinProvider;
use GoPhp\Env\Environment;
use GoPhp\Error\AbortExecutionError;
use GoPhp\Error\InternalError;
use GoPhp\Error\PanicError;
use GoPhp\Error\RuntimeError;
use GoPhp\ErrorHandler\ErrorHandler;
use GoPhp\ErrorHandler\OutputToStream;
use GoPhp\GoType\ArrayType;
use GoPhp\GoType\FuncType;
use GoPhp\GoType\GoType;
use GoPhp\GoType\InterfaceType;
use GoPhp\GoType\MapType;
use GoPhp\GoType\NamedType;
use GoPhp\GoType\PointerType;
use GoPhp\GoType\RefType;
use GoPhp\GoType\SliceType;
use GoPhp\GoType\StructType;
use GoPhp\GoType\WrappedType;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\Array\ArrayBuilder;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\BuiltinFuncValue;
use GoPhp\GoValue\Complex\UntypedComplexValue;
use GoPhp\GoValue\ConstInvokable;
use GoPhp\GoValue\Float\UntypedFloatValue;
use GoPhp\GoValue\Func\FuncValue;
use GoPhp\GoValue\Func\Param;
use GoPhp\GoValue\Func\Params;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Int\IntNumber;
use GoPhp\GoValue\Int\UntypedIntValue;
use GoPhp\GoValue\Interface\InterfaceBuilder;
use GoPhp\GoValue\Invokable;
use GoPhp\GoValue\Map\MapBuilder;
use GoPhp\GoValue\Map\MapLookupValue;
use GoPhp\GoValue\PointerValue;
use GoPhp\GoValue\RecoverableInvokable;
use GoPhp\GoValue\Sequence;
use GoPhp\GoValue\Slice\SliceBuilder;
use GoPhp\GoValue\Slice\SliceValue;
use GoPhp\GoValue\Sliceable;
use GoPhp\GoValue\StringValue;
use GoPhp\GoValue\Struct\StructBuilder;
use GoPhp\GoValue\Struct\StructValue;
use GoPhp\GoValue\TupleValue;
use GoPhp\GoValue\TypeValue;
use GoPhp\GoValue\UntypedNilValue;
use GoPhp\GoValue\WrappedValue;
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
    private Iota $iota;
    private PanicPointer $panicPointer;
    private Environment $env;
    private JumpStack $jumpStack;
    private DeferredStack $deferredStack;
    private ScopeResolver $scopeResolver;
    private bool $constContext = false;
    private int $switchContext = 0;
    /** @var array<InvokableCall> */
    private array $initializers = [];
    private readonly Argv $argv;
    private readonly ErrorHandler $errorHandler;
    private ?FuncValue $entryPoint = null;
    private ImportHandler $importHandler;
    private readonly string $source;
    private readonly StreamProvider $streams;
    private readonly FuncTypeValidator $entryPointValidator;
    private readonly FuncTypeValidator $initValidator;

    /**
     * @param list<GoValue> $argv
     */
    public function __construct(
        string $source,
        ?BuiltinProvider $builtin = null,
        array $argv = [],
        ?ErrorHandler $errorHandler = null,
        StreamProvider $streams = new StdStreamProvider(),
        FuncTypeValidator $entryPointValidator = new ZeroArityValidator(MAIN_FUNC_NAME, MAIN_PACK_NAME),
        FuncTypeValidator $initValidator = new ZeroArityValidator(INIT_FUNC_NAME),
        EnvVarSet $envVars = new EnvVarSet(),
        bool $toplevel = false,
    ) {
        $this->jumpStack = new JumpStack();
        $this->deferredStack = new DeferredStack();
        $this->scopeResolver = new ScopeResolver();
        $this->importHandler = new ImportHandler($envVars);
        $this->streams = $streams;
        $this->entryPointValidator = $entryPointValidator;
        $this->initValidator = $initValidator;


        $this->errorHandler = $errorHandler === null
            ? new OutputToStream($this->streams->stderr())
            : $errorHandler;

        if ($builtin === null) {
            $builtin = new StdBuiltinProvider($this->streams->stderr());
        }

        $this->iota = $builtin->iota();
        $this->panicPointer = $builtin->panicPointer();
        $this->env = new Environment($builtin->env());
        $this->argv = (new ArgvBuilder($argv))->build();
        $this->source = $toplevel ? self::wrapSource($source) : $source;
    }

    /**
     * @throws InternalError unexpected error during execution
     */
    public function run(): ExitCode
    {
        try {
            $ast = $this->parseSourceToAst($this->source);
            $this->setAst($ast);
            $this->evalDeclsInOrder();

            if ($this->entryPoint === null) {
                throw RuntimeError::noEntryPoint($this->entryPointValidator->getFuncName());
            }

            $call = new InvokableCall($this->entryPoint, $this->argv);
            $this->callFunc($call);
        } catch (RuntimeError $error) {
            $this->errorHandler->onError($error->getMessage());
            return ExitCode::Failure;
        } catch (AbortExecutionError) {
            return ExitCode::Failure;
        }

        return ExitCode::Success;
    }

    private function evalDeclsInOrder(): void
    {
        foreach ($this->ast->imports as $import) {
            $this->evalImportDeclStmt($import);
        }

        $mapping = [
            ConstDecl::class => [],
            VarDecl::class => [],
            TypeDecl::class => [],
            FuncDecl::class => [],
            MethodDecl::class => [],
        ];

        foreach ($this->ast->decls as $decl) {
            $key = match (true) {
                $decl instanceof ConstDecl,
                $decl instanceof VarDecl,
                $decl instanceof TypeDecl,
                $decl instanceof FuncDecl,
                $decl instanceof MethodDecl, => $decl::class,
                default => throw RuntimeError::nonDeclarationOnTopLevel(),
            };

            $mapping[$key][] = $decl;
        }

        $this->scopeResolver->enterPackageScope();

        foreach ($mapping[TypeDecl::class] as $decl) {
            /** @var TypeDecl $decl */
            $this->evalTypeDeclStmt($decl);
        }

        foreach ($mapping[ConstDecl::class] as $decl) {
            /** @var ConstDecl $decl */
            $this->evalConstDeclStmt($decl);
        }

        foreach ($mapping[VarDecl::class] as $decl) {
            /** @var VarDecl $decl */
            $this->evalVarDeclStmt($decl);
        }

        foreach ($mapping[FuncDecl::class] as $decl) {
            /** @var FuncDecl $decl */
            $this->evalFuncDeclStmt($decl);
        }

        foreach ($mapping[MethodDecl::class] as $decl) {
            /** @var MethodDecl $decl */
            $this->evalMethodDeclStmt($decl);
        }

        foreach ($this->initializers as $initializer) {
            $this->callFunc($initializer);
        }

        $this->initializers = [];
        $this->scopeResolver->exitPackageScope();
    }

    private function evalImportDeclStmt(ImportDecl $decl): void
    {
        $mainAst = $this->ast;

        foreach (iter_spec($decl->spec) as $spec) {
            /** @var StringValue $path */
            $path = $this->evalExpr($spec->path);
            $importedFiles = $this->importHandler->importFromPath($path->unwrap());

            foreach ($importedFiles as $source) {
                $ast = $this->parseSourceToAst($source);
                $this->setAst($ast);
                $this->evalDeclsInOrder();
            }
        }

        $this->setAst($mainAst);
    }

    private function setAst(Ast $ast): void
    {
        $this->ast = $ast;
        $this->scopeResolver->currentPackage = $ast->package->identifier->name;
    }

    private function parseSourceToAst(string $source): Ast
    {
        $parser = new Parser($source);

        /** @var Ast $ast */
        $ast = $parser->parse();

        if ($parser->hasErrors()) {
            foreach ($parser->getErrors() as $error) {
                $this->errorHandler->onError((string) $error);
            }

            throw new AbortExecutionError();
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
            $stmt instanceof FuncDecl => throw RuntimeError::nestedFunction(),
            default => throw InternalError::unreachable($stmt),
        };
    }

    private function evalConstDeclStmt(ConstDecl $decl): None
    {
        $this->constContext = true;
        $initExprs = [];

        foreach (iter_spec($decl->spec) as $j => $spec) {
            $this->iota->set($j);
            $type = null;

            if ($spec->type !== null) {
                $type = $this->resolveType($spec->type);
            }

            if ($type !== null && !$type instanceof NamedType) {
                throw RuntimeError::invalidConstantType($type);
            }

            if (!empty($spec->initList->exprs)) {
                $initExprs = $spec->initList->exprs;
            }

            if (\count($initExprs) > \count($spec->identList->idents)) {
                throw RuntimeError::extraInitExpr();
            }

            foreach ($spec->identList->idents as $i => $ident) {
                $value = isset($initExprs[$i])
                    ? $this->tryEvalConstExpr($initExprs[$i])
                    : null;

                if (!$value instanceof AddressableValue) {
                    if ($value === null) {
                        throw RuntimeError::uninitialisedConstant($ident->name);
                    }

                    throw InternalError::unexpectedValue($value);
                }

                $this->checkNonDeclarableNames($ident->name);

                $this->env->defineConst(
                    $ident->name,
                    $value->copy(),
                    $type?->reify() ?? $value->type(),
                    $this->scopeResolver->resolveDefinitionScope(),
                );
            }
        }

        $this->constContext = false;

        return None::None;
    }

    private function evalVarDeclStmt(VarDecl $decl): None
    {
        foreach (iter_spec($decl->spec) as $spec) {
            $type = null;
            if ($spec->type !== null) {
                $type = $this->resolveType($spec->type);
            }

            $values = [];
            $identsLen = \count($spec->identList->idents);

            if ($spec->initList === null) {
                if ($type === null) {
                    throw RuntimeError::uninitilisedVarWithNoType();
                }

                for ($i = 0; $i < $identsLen; ++$i) {
                    $values[] = $type->zeroValue();
                }
            } else {
                $values = $this->collectValuesFromExprList($spec->initList, $identsLen);
            }

            foreach ($spec->identList->idents as $i => $ident) {
                $this->defineVar(
                    $ident->name,
                    $values[$i]->copy(),
                    $type,
                );
            }
        }

        return None::None;
    }

    private function evalTypeDeclStmt(TypeDecl $decl): None
    {
        foreach (iter_spec($decl->spec) as $spec) {
            match (true) {
                $spec->value instanceof AliasDecl => $this->evalAliasDeclStmt($spec->value),
                $spec->value instanceof TypeDef => $this->evalTypeDefStmt($spec->value),
            };
        }

        return None::None;
    }

    private function evalAliasDeclStmt(AliasDecl $decl): void
    {
        $alias = $decl->ident->name;
        $typeValue = new TypeValue($this->resolveType($decl->type));

        $this->checkNonDeclarableNames($alias);

        $this->env->defineTypeAlias(
            $alias,
            $typeValue,
            $this->scopeResolver->resolveDefinitionScope(),
        );
    }

    private function evalTypeDefStmt(TypeDef $stmt): void
    {
        $name = $stmt->ident->name;
        $type = $this->resolveType($stmt->type);
        $namespace = $this->scopeResolver->resolveDefinitionScope();

        $typeValue = new TypeValue(new WrappedType($name, $namespace, $type));

        $this->checkNonDeclarableNames($name);

        $this->env->defineType($name, $typeValue, $namespace);
    }

    private function evalMethodDeclStmt(MethodDecl $decl): void
    {
        if ($decl->body === null) {
            throw InternalError::unimplemented();
        }

        $receiverParam = $this->resolveParamsFromAstParams($decl->receiver);

        if ($receiverParam->len !== 1) {
            throw RuntimeError::multipleReceivers();
        }

        $receiver = $receiverParam[0];
        $receiverType = $receiver->type;

        if ($receiverType instanceof PointerType) {
            $receiverType = $receiverType->pointsTo;
        }

        // technically WrappedType is the only type that can be a receiver
        if (!$receiverType instanceof WrappedType) {
            throw RuntimeError::invalidReceiverType($receiverType);
        }

        $funcValue = $this->constructFuncValue($decl->signature, $decl->body, $receiver);
        $currentPackage = $this->scopeResolver->currentPackage;

        if (!$receiverType->isLocal($currentPackage)) {
            throw RuntimeError::methodOnNonLocalType($receiverType->name());
        }

        $this->env->registerMethod($decl->name->name, $funcValue, $receiverType);
    }

    private function evalFuncDeclStmt(FuncDecl $decl): void
    {
        if ($decl->body === null) {
            throw InternalError::unimplemented();
        }

        $funcValue = $this->constructFuncValue($decl->signature, $decl->body);
        $currentPackage = $this->scopeResolver->currentPackage;

        if ($this->entryPointValidator->supports($decl->name->name, $currentPackage)) {
            $this->entryPointValidator->validate($funcValue->type);
            $this->entryPoint = $funcValue;
        }

        if ($this->initValidator->supports($decl->name->name, $currentPackage)) {
            $this->initValidator->validate($funcValue->type);
            // the identifier itself is not declared
            // init functions cannot be referred to from anywhere in a program
            $this->initializers[] = new InvokableCall($funcValue, Argv::fromEmpty());

            return;
        }

        $this->env->defineFunc($decl->name->name, $funcValue, $currentPackage);
    }

    private function constructFuncValue(AstSignature $signature, BlockStmt $blockStmt, ?Param $receiver = null): FuncValue
    {
        $type = $this->resolveTypeFromAstSignature($signature);

        return FuncValue::fromBody(
            body: function (Environment $env, string $withPackage) use ($blockStmt, $type): StmtJump {
                $currentPackage = $withPackage;
                $prevPackage = $this->scopeResolver->currentPackage;
                $this->scopeResolver->currentPackage = $currentPackage;
                $jump = $this->evalBlockStmt($blockStmt, $env);
                $this->scopeResolver->currentPackage = $prevPackage;

                return $jump;
            },
            type: $type,
            enclosure: $this->env,
            receiver: $receiver,
            namespace: $this->scopeResolver->currentPackage,
        );
    }

    private function evalDeferStmt(DeferStmt $stmt): None
    {
        $fn = $this->evalCallExprWithoutCall($stmt->expr);
        $this->deferredStack->push($fn);

        return None::None;
    }

    private function evalCallExprWithoutCall(CallExpr $expr): InvokableCall
    {
        $func = $this->evalExpr($expr->expr);

        if (!$func instanceof Invokable) {
            throw RuntimeError::nonFunctionCall($func);
        }

        if ($this->constContext && !$func instanceof ConstInvokable) {
            throw RuntimeError::nonConstantExpr($func);
        }

        /** @var Invokable $func */
        $exprLen = \count($expr->args->exprs);
        $nValuedContext = 1;
        $argvBuilder = new ArgvBuilder();
        $startFrom = 0;

        if (
            $func instanceof BuiltinFuncValue
            && $func->func->expectsTypeAsFirstArg()
            && isset($expr->args->exprs[0])
        ) {
            if (!$expr->args->exprs[0] instanceof AstType) {
                throw InternalError::unexpectedValue($expr->args->exprs[0]);
            }

            $argvBuilder->add(new TypeValue($this->resolveType($expr->args->exprs[0])));
            ++$startFrom;
        }

        for ($i = $startFrom; $i < $exprLen; ++$i) {
            $arg = $this->evalExpr($expr->args->exprs[$i]);

            if ($arg instanceof TupleValue) {
                if ($exprLen !== 1) {
                    throw RuntimeError::multipleValueInSingleContext($arg);
                }

                $nValuedContext = $arg->len;

                foreach ($arg->values as $value) {
                    $argvBuilder->add($value->copy());
                }

                break;
            }

            $argvBuilder->add($arg->copy());
        }

        if ($expr->ellipsis !== null) {
            if ($nValuedContext > 1) {
                throw RuntimeError::cannotSplatMultipleValuedReturn($nValuedContext);
            }

            $argvBuilder->markUnpacked($func);
        }

        return new InvokableCall($func, $argvBuilder->build());
    }

    private function evalCallExpr(CallExpr $expr): GoValue
    {
        $fn = $this->evalCallExprWithoutCall($expr);

        return $this->callFunc($fn);
    }

    private function callFunc(InvokableCall $fn): GoValue
    {
        $this->jumpStack->push(new JumpHandler());
        $this->deferredStack->newContext();

        try {
            $value = $fn();
            $this->releaseDeferredStack();

            return $value;
        } catch (PanicError $panic) {
            $this->panicPointer->panic = $panic;
            $this->releaseDeferredStack();

            if ($this->panicPointer->panic === null) {
                if ($fn->func instanceof RecoverableInvokable) {
                    return $fn->func->zeroReturnValue();
                }
            }

            throw $panic;
        } finally {
            $this->jumpStack->pop();
        }
    }

    private function evalIndexExpr(IndexExpr $expr): GoValue
    {
        $sequence = $this->evalExpr($expr->expr);
        $sequence = normalize_unwindable($sequence);

        if (!$sequence instanceof Sequence) {
            throw RuntimeError::cannotIndex($sequence->type());
        }

        $index = $this->evalExpr($expr->index);

        return $sequence->get($index);
    }

    private function evalSliceExpr(SimpleSliceExpr|FullSliceExpr $expr): StringValue|SliceValue
    {
        $sequence = $this->evalExpr($expr->expr);

        if (!$sequence instanceof Sliceable) {
            throw RuntimeError::cannotSlice($sequence->type());
        }

        $low = $this->getSliceExprIndex($expr->low);
        $high = $this->getSliceExprIndex($expr->high);
        $max = $expr instanceof FullSliceExpr
            ? $this->getSliceExprIndex($expr->max)
            : null;

        return $sequence->slice($low, $high, $max);
    }

    private function getSliceExprIndex(?Expr $expr): ?int
    {
        if ($expr === null) {
            return null;
        }

        $index = $this->evalExpr($expr);
        assert_index_int($index, SliceValue::NAME);

        return (int) $index->unwrap();
    }

    private function evalEmptyStmt(EmptyStmt $stmt): None
    {
        return None::None;
    }

    private function evalBreakStmt(BreakStmt $stmt): BreakJump
    {
        return new BreakJump();
    }

    private function evalFallthroughStmt(FallthroughStmt $stmt): FallthroughJump
    {
        if ($this->switchContext <= 0) {
            throw RuntimeError::misplacedFallthrough();
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

        return None::None;
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
            $stmtJump = None::None;
            $len = \count($stmtList->stmts);
            $gotoIndex = 0;

            for ($i = 0; $i < $len; ++$i) {
                $stmt = $stmtList->stmts[$i];

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
                        /** @psalm-suppress LoopInvalidation */
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
                    throw RuntimeError::misplacedFallthrough();
                }
            }

            if ($stmtJump instanceof GotoJump) {
                throw RuntimeError::undefinedLabel($jump->getLabel());
            }

            return $stmtJump;
        });
    }

    /**
     * @param callable(): StmtJump $code
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
                    throw RuntimeError::multipleValueInSingleContext($value);
                }

                return ReturnJump::fromTuple($value);
            }

            $values[] = $value;
        }

        if (\count($values) === ReturnJump::LEN_SINGLE) {
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

        return None::None;
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

            return None::None;
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
                        default => throw InternalError::unreachable($stmt->iteration->condition),
                    };

                    $post = $stmt->iteration->post ?? null;
                    break;
                default:
                    throw throw InternalError::unreachable($stmt->iteration);
            }

            while ($condition === null || self::isTrue($this->evalExpr($condition))) {
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
                        return None::None;
                    case $stmtJump instanceof ReturnJump || $stmtJump instanceof GotoJump:
                        return $stmtJump;
                    default:
                        throw InternalError::unreachable($stmtJump);
                }

                if ($post !== null) {
                    $this->evalStmt($post);
                }
            }

            return None::None;
        });
    }

    private function evalExprSwitchStmt(ExprSwitchStmt $stmt): StmtJump
    {
        return $this->evalWithEnvWrap(null, function () use ($stmt): StmtJump {
            ++$this->switchContext;

            if ($stmt->init !== null) {
                $this->evalStmt($stmt->init);
            }

            $condition = $stmt->condition === null
                ? BoolValue::true()
                : $this->evalExpr($stmt->condition);

            $stmtJump = None::None;
            $defaultCaseIndex = null;

            foreach ($stmt->caseClauses as $i => $caseClause) {
                if ($caseClause->case instanceof DefaultCase) {
                    if ($defaultCaseIndex !== null) {
                        throw RuntimeError::multipleDefaults();
                    }

                    $defaultCaseIndex = $i;
                    continue;
                }

                foreach ($caseClause->case->exprList->exprs as $expr) {
                    $caseCondition = $this->evalExpr($expr);
                    $equal = $caseCondition->operateOn(Operator::EqEq, $condition);

                    if ($equal instanceof BoolValue && $equal->isTrue()) {
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
            $stmtJump = None::None;
        }

        return $stmtJump;
    }

    private function evalSwitchWithFallthrough(ExprSwitchStmt|TypeSwitchStmt $stmt, int $fromCase): StmtJump
    {
        $stmtJump = None::None;

        for (
            $i = $fromCase,
            $caseClausesLen = \count($stmt->caseClauses);
            $i < $caseClausesLen;
            $i++
        ) {
            $stmtJump = $this->evalStmtList($stmt->caseClauses[$i]->stmtList);

            if ($stmtJump instanceof FallthroughJump) {
                if ($i === $caseClausesLen - 1) {
                    throw RuntimeError::fallthroughFinalCase();
                }

                $stmtJump = None::None;

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
            throw RuntimeError::invalidRangeValue($range);
        }

        /**
         * @var bool $define
         * @var Ident[] $iterVars
         */
        [$define, $iterVars] = match (true) {
            $iteration->list instanceof ExprList => [false, $iteration->list->exprs],
            $iteration->list instanceof IdentList => [true, $iteration->list->idents],
            default => [false, []],
        };

        [$keyVar, $valVar] = match (\count($iterVars)) {
            0 => [null, null],
            1 => [$iterVars[0], null],
            2 => $iterVars,
            default => throw RuntimeError::tooManyRangeVars(),
        };

        foreach ($range->iter() as $key => $value) {
            /**
             * @var AddressableValue $key
             * @var AddressableValue $value
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

            switch (true) {
                case $stmtJump instanceof None:
                    break;
                case $stmtJump instanceof ContinueJump:
                    continue 2;
                case $stmtJump instanceof BreakJump:
                    return None::None;
                case $stmtJump instanceof ReturnJump || $stmtJump instanceof GotoJump:
                    return $stmtJump;
                default:
                    throw InternalError::unreachable($stmtJump);
            }
        }

        return None::None;
    }

    private function evalIncDecStmt(IncDecStmt $stmt): None
    {
        $this
            ->evalExpr($stmt->lhs)
            ->mutate(
                Operator::fromAst($stmt->op),
                new UntypedIntValue(1)
            );

        return None::None;
    }

    private function evalAssignmentStmt(AssignmentStmt $stmt): None
    {
        $op = Operator::fromAst($stmt->op);

        if (!$op->isAssignment()) {
            throw RuntimeError::expectedAssignmentOperator($op);
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

        return None::None;
    }

    private function evalShortVarDeclStmt(ShortVarDecl $stmt): None
    {
        $len = \count($stmt->identList->idents);
        $values = $this->collectValuesFromExprList($stmt->exprList, $len);

        $hasNew = false;
        foreach ($stmt->identList->idents as $i => $ident) {
            $envValue = $this->env->tryGetFromSameScope($ident->name);

            if ($envValue === null) {
                $hasNew = true;
                $this->defineVar($ident->name, $values[$i]);
            } else {
                $envValue->unwrap()->mutate(Operator::Eq, $values[$i]);
            }
        }

        if (!$hasNew) {
            throw RuntimeError::noNewVarsInShortAssignment();
        }

        return None::None;
    }

    private function collectValuesFromExprList(ExprList $exprList, int $expectedLen): array
    {
        $value = $this->evalExpr($exprList->exprs[0]);
        $exprLen = \count($exprList->exprs);

        if ($value instanceof TupleValue) {
            if ($exprLen !== 1) {
                throw RuntimeError::multipleValueInSingleContext($value);
            }

            if ($value->len !== $expectedLen) {
                throw RuntimeError::assignmentMismatch($expectedLen, $value->len);
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
            throw RuntimeError::assignmentMismatch($expectedLen, $exprLen);
        }

        $values = [$value];

        for ($i = 1; $i < $expectedLen; ++$i) {
            $value = $this->evalExpr($exprList->exprs[$i]);

            if ($value instanceof TupleValue) {
                throw RuntimeError::multipleValueInSingleContext($value);
            }

            $values[] = $value;
        }

        return $values;
    }

    private function evalExpr(Expr $expr): GoValue
    {
        $value = $this->tryEvalConstExpr($expr);

        if ($value !== null) {
            return $value;
        }

        $value = match (true) {
            $expr instanceof CallExpr => $this->evalCallExpr($expr),
            $expr instanceof IndexExpr => $this->evalIndexExpr($expr),
            $expr instanceof SimpleSliceExpr => $this->evalSliceExpr($expr),
            $expr instanceof FullSliceExpr => $this->evalSliceExpr($expr),
            $expr instanceof CompositeLit => $this->evalCompositeLit($expr),
            $expr instanceof FuncLit => $this->evalFuncLit($expr),
            default => throw InternalError::unreachable($expr),
        };

        if ($this->constContext) {
            throw RuntimeError::nonConstantExpr($value);
        }

        return $value;
    }

    private function tryEvalConstExpr(Expr $expr): ?GoValue
    {
        return match (true) {
            // literals
            $expr instanceof RuneLit => $this->evalRuneLit($expr),
            $expr instanceof StringLit => $this->evalStringLit($expr),
            $expr instanceof RawStringLit => $this->evalRawStringLit($expr),
            $expr instanceof IntLit => $this->evalIntLit($expr),
            $expr instanceof FloatLit => $this->evalFloatLit($expr),
            $expr instanceof ImagLit => $this->evalImagLit($expr),
            // possibly const expr
            $expr instanceof Ident => $this->evalIdent($expr),
            $expr instanceof CallExpr => $this->evalCallExpr($expr),
            $expr instanceof UnaryExpr => $this->evalUnaryExpr($expr),
            $expr instanceof BinaryExpr => $this->evalBinaryExpr($expr),
            $expr instanceof GroupExpr => $this->evalGroupExpr($expr),
            $expr instanceof SelectorExpr => $this->evalSelectorExpr($expr),
            default => null,
        };
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
            $expr instanceof SelectorExpr => $this->evalSelectorExpr($expr),
            default => throw RuntimeError::cannotAssign($this->evalExpr($expr)),
        };
    }

    private function evalCompositeLit(CompositeLit $lit): GoValue
    {
        if ($lit->type === null) {
            throw InternalError::unexpectedValue($lit);
        }

        $type = $this->resolveType($lit->type, true);

        return $this->resolveCompositeLitWithType($lit, $type);
    }

    private function resolveCompositeLitWithType(CompositeLit $lit, GoType $type): GoValue
    {
        $wrapper = null;

        if ($type instanceof WrappedType) {
            $wrapper = $type->valueCallback();
            $type = $type->unwind();
        }

        $builder = match (true) {
            $type instanceof ArrayType => ArrayBuilder::fromType($type),
            $type instanceof SliceType => SliceBuilder::fromType($type),
            $type instanceof MapType => MapBuilder::fromType($type),
            $type instanceof StructType => StructBuilder::fromType($type),
            $type instanceof InterfaceType => InterfaceBuilder::fromType($type),
            default => throw InternalError::unreachable($type),
        };

        foreach ($lit->elementList->elements ?? [] as $element) {
            $builder->push($element, $this->evalExpr(...));
        }

        $builtValue = $builder->build();

        if ($wrapper !== null) {
            $builtValue = $wrapper($builtValue);
        }

        return $builtValue;
    }

    private function evalFuncLit(FuncLit $lit): FuncValue
    {
        return $this->constructFuncValue($lit->type->signature, $lit->body);
    }

    private function evalRuneLit(RuneLit $lit): UntypedIntValue
    {
        return UntypedIntValue::fromRune(\trim($lit->rune, '\''));
    }

    private function evalStringLit(StringLit $lit): StringValue
    {
        return new StringValue(\trim($lit->str, '"'));
    }

    private function evalRawStringLit(RawStringLit $lit): StringValue
    {
        return new StringValue(\trim($lit->str, '`'));
    }

    private function evalIntLit(IntLit $lit): UntypedIntValue
    {
        return UntypedIntValue::fromString($lit->digits);
    }

    private function evalFloatLit(FloatLit $lit): UntypedFloatValue
    {
        return UntypedFloatValue::fromString($lit->digits);
    }

    private function evalImagLit(ImagLit $lit): UntypedComplexValue
    {
        return UntypedComplexValue::fromString($lit->digits);
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
        // package access
        if (
            $expr->expr instanceof Ident
            && $this->env->isNamespaceDefined($expr->expr->name)
        ) {
            return $this->env->get($expr->selector->name, $expr->expr->name, implicit: false)->unwrap();
        }

        // struct access
        $value = $this->evalExpr($expr->expr);
        if (!$value instanceof AddressableValue) {
            throw InternalError::unexpectedValue($value::class, AddressableValue::class);
        }

        $method = $this->env->getMethod(
            $expr->selector->name,
            $value->type(),
        );

        if ($method == null && $value instanceof WrappedValue) {
            $method = $this->env->getMethod(
                $expr->selector->name,
                $value->underlyingValue->type(),
            );
        }

        if ($method !== null) {
            $method->bind($value);

            return $method;
        }

        $originalType = $value->type();

        do {
            $check = false;
            $value = normalize_unwindable($value);

            if ($value instanceof PointerValue) {
                $value = $value->getPointsTo();
                $check = true;
            }
        } while ($check);

        if (!$value instanceof StructValue) {
            if (!$value instanceof AddressableValue) {
                throw InternalError::unreachable($value);
            }

            throw RuntimeError::undefinedFieldAccess(
                $value->getName(),
                $expr->selector->name,
                $originalType,
            );
        }

        return $value->accessField($expr->selector->name);
    }

    private function evalIdent(Ident $ident): GoValue
    {
        $value = $this->env->get(
            $ident->name,
            $this->scopeResolver->currentPackage
        )->unwrap();

        if ($value === $this->iota && !$this->constContext) {
            throw RuntimeError::iotaMisuse();
        }

        return $value;
    }

    private function evalPointerUnaryExpr(UnaryExpr $expr): GoValue
    {
        $value = $this->evalUnaryExpr($expr);

        if ($expr->op->value !== Operator::Mul->value) {
            throw RuntimeError::cannotAssign($value);
        }

        return $value;
    }

    private static function isTrue(GoValue $value): bool
    {
        if (!$value instanceof BoolValue) {
            throw RuntimeError::valueOfWrongType($value, NamedType::Bool);
        }

        return $value->isTrue();
    }

    private function resolveType(AstType $type, bool $composite = false): GoType
    {
        return match (true) {
            $type instanceof SingleTypeName => $this->resolveTypeFromSingleName($type),
            $type instanceof QualifiedTypeName => $this->resolveTypeFromQualifiedName($type),
            $type instanceof AstFuncType => $this->resolveTypeFromAstSignature($type->signature),
            $type instanceof AstArrayType => $this->resolveArrayType($type, $composite),
            $type instanceof AstSliceType => $this->resolveSliceType($type, $composite),
            $type instanceof AstMapType => $this->resolveMapType($type, $composite),
            $type instanceof AstPointerType => $this->resolvePointerType($type, $composite),
            $type instanceof AstStructType => $this->resolveStructType($type, $composite),
            $type instanceof AstInterfaceType => $this->resolveInterfaceType($type, $composite),
            default => throw InternalError::unreachable($type),
        };
    }

    private function resolveTypeFromSingleName(SingleTypeName $type): GoType
    {
        return $this->getTypeFromEnv(
            $type->name->name,
            $this->scopeResolver->currentPackage,
        );
    }

    private function resolveTypeFromQualifiedName(QualifiedTypeName $type): GoType
    {
        return $this->getTypeFromEnv(
            $type->typeName->name->name,
            $type->packageName->name,
        );
    }

    private function getTypeFromEnv(string $name, string $namespace): GoType
    {
        $value = $this->env->get($name, $namespace)->unwrap();

        if (!$value instanceof TypeValue) {
            throw RuntimeError::valueIsNotType($value);
        }

        return $value->unwrap();
    }

    private function resolveTypeFromAstSignature(AstSignature $signature): FuncType
    {
        return new FuncType(...$this->resolveParamsFromAstSignature($signature));
    }

    private function resolveArrayType(AstArrayType $arrayType, bool $composite): ArrayType
    {
        $elemType = $this->resolveType($arrayType->elemType, $composite);

        if ($arrayType->len instanceof Expr) {
            $len = $this->tryEvalConstExpr($arrayType->len) ?? throw RuntimeError::invalidArrayLen();

            if (!$len instanceof IntNumber) {
                throw RuntimeError::nonIntegerArrayLen($len);
            }

            return ArrayType::fromLen($elemType, $len->unwrap());
        }

        if ($arrayType->len->value === Token::Ellipsis->value && $composite) {
            return ArrayType::unfinished($elemType);
        }

        throw InternalError::unreachable($arrayType);
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

    private function resolveStructType(AstStructType $structType, bool $composite): StructType
    {
        /** @var array<string, GoType> $fields */
        $fields = [];

        foreach ($structType->fieldDecls as $fieldDecl) {
            if ($fieldDecl->identList === null) {
                // fixme add anonymous fields
                throw InternalError::unimplemented();
            }

            if ($fieldDecl->type === null) {
                throw InternalError::unimplemented();
            }

            $type = $this->resolveType($fieldDecl->type, $composite);

            foreach ($fieldDecl->identList->idents as $ident) {
                if (isset($fields[$ident->name])) {
                    throw RuntimeError::redeclaredName($ident->name);
                }

                $fields[$ident->name] = $type;
            }
        }

        return new StructType($fields);
    }

    private function resolveInterfaceType(AstInterfaceType $interfaceType, bool $composite): InterfaceType
    {
        // fixme use composite param
        $methods = [];
        foreach ($interfaceType->items as $item) {
            if ($item instanceof TypeTerm) {
                throw InternalError::unimplemented();
            }

            if ($item instanceof MethodElem) {
                if (isset($methods[$item->methodName->name])) {
                    throw RuntimeError::duplicateMethod($item->methodName->name);
                }

                $methods[$item->methodName->name] = $this->resolveTypeFromAstSignature($item->signature);
            }
        }

        return new InterfaceType($methods, $this->env);
    }

    /**
     * @return array{Params, Params}
     */
    private function resolveParamsFromAstSignature(AstSignature $signature): array
    {
        return [
            $this->resolveParamsFromAstParams($signature->params),
            match (true) {
                $signature->result === null => Params::fromEmpty(),
                $signature->result instanceof AstType => Params::fromParam(new Param($this->resolveType($signature->result))),
                $signature->result instanceof AstParams => $this->resolveParamsFromAstParams($signature->result),
            },
        ];
    }

    private function resolveParamsFromAstParams(AstParams $astParams): Params
    {
        $params = [];
        foreach ($astParams->paramList as $paramDecl) {
            if ($paramDecl->identList === null) {
                $params[] = new Param(
                    $this->resolveType($paramDecl->type),
                    null,
                    $paramDecl->ellipsis !== null,
                );
                continue;
            }

            foreach ($paramDecl->identList->idents as $ident) {
                $params[] = new Param(
                    $this->resolveType($paramDecl->type),
                    $ident->name,
                    $paramDecl->ellipsis !== null,
                );
            }
        }

        return new Params($params);
    }

    private function defineVar(string $name, GoValue $value, ?GoType $type = null): void
    {
        $this->checkNonDeclarableNames($name);

        if ($value instanceof UntypedNilValue && $type instanceof RefType) {
            $value = $type->zeroValue();
        } else {
            /** @var AddressableValue $value */
            $value = $value->copy();
        }

        $this->env->defineVar(
            $name,
            $value,
            ($type ?? $value->type())->reify(),
            $this->scopeResolver->resolveDefinitionScope(),
        );
    }

    private function checkNonDeclarableNames(string $name): void
    {
        /** @var list<FuncTypeValidator> $validators */
        $validators = [$this->entryPointValidator, $this->initValidator];

        foreach ($validators as $validator) {
            if ($validator->supports($name, $this->scopeResolver->currentPackage)) {
                throw RuntimeError::nameMustBeFunc($name);
            }
        }
    }

    private function releaseDeferredStack(): void
    {
        foreach ($this->deferredStack->iter() as $deferredFunc) {
            $this->callFunc($deferredFunc);
        }
    }

    private function wrapSource(string $source): string
    {
        return <<<GO
        package {$this->entryPointValidator->getPackageName()}
        
        func {$this->entryPointValidator->getFuncName()}() {
            {$source}
        }
        GO;
    }
}
