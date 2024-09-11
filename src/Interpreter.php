<?php

declare(strict_types=1);

namespace GoPhp;

use GoParser\Ast\AliasDecl;
use GoParser\Ast\DefaultCase;
use GoParser\Ast\Expr\BinaryExpr;
use GoParser\Ast\Expr\CallExpr;
use GoParser\Ast\Expr\CompositeLit;
use GoParser\Ast\Expr\Expr;
use GoParser\Ast\Expr\FloatLit;
use GoParser\Ast\Expr\FullSliceExpr;
use GoParser\Ast\Expr\FuncLit;
use GoParser\Ast\Expr\GroupExpr;
use GoParser\Ast\Expr\Ident;
use GoParser\Ast\Expr\ImagLit;
use GoParser\Ast\Expr\IndexExpr;
use GoParser\Ast\Expr\IntLit;
use GoParser\Ast\Expr\RawStringLit;
use GoParser\Ast\Expr\RuneLit;
use GoParser\Ast\Expr\SelectorExpr;
use GoParser\Ast\Expr\SimpleSliceExpr;
use GoParser\Ast\Expr\StringLit;
use GoParser\Ast\Expr\Type as AstType;
use GoParser\Ast\Expr\TypeAssertionExpr;
use GoParser\Ast\Expr\UnaryExpr;
use GoParser\Ast\ExprCaseClause;
use GoParser\Ast\ExprList;
use GoParser\Ast\File as Ast;
use GoParser\Ast\ForClause;
use GoParser\Ast\IdentList;
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
use GoParser\Parser;
use GoPhp\Builtin\BuiltinFunc\Marker\ExpectsTypeAsFirstArg;
use GoPhp\Builtin\BuiltinProvider;
use GoPhp\Builtin\Iota;
use GoPhp\Builtin\StdBuiltinProvider;
use GoPhp\Debug\CallStackCollectorDebugger;
use GoPhp\Debug\Debugger;
use GoPhp\Env\Environment;
use GoPhp\Error\AbortExecutionError;
use GoPhp\Error\InternalError;
use GoPhp\Error\PanicError;
use GoPhp\Error\ParserError;
use GoPhp\Error\RuntimeError;
use GoPhp\ErrorHandler\ErrorHandler;
use GoPhp\ErrorHandler\StreamWriter;
use GoPhp\GoType\ArrayType;
use GoPhp\GoType\GoType;
use GoPhp\GoType\InterfaceType;
use GoPhp\GoType\MapType;
use GoPhp\GoType\NamedType;
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
use GoPhp\GoValue\Func\Receiver;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Int\UntypedIntValue;
use GoPhp\GoValue\Interface\InterfaceBuilder;
use GoPhp\GoValue\Interface\InterfaceValue;
use GoPhp\GoValue\Invokable;
use GoPhp\GoValue\Map\MapBuilder;
use GoPhp\GoValue\Map\MapLookupValue;
use GoPhp\GoValue\PointerValue;
use GoPhp\GoValue\Sequence;
use GoPhp\GoValue\Slice\SliceBuilder;
use GoPhp\GoValue\Slice\SliceValue;
use GoPhp\GoValue\Sliceable;
use GoPhp\GoValue\String\UntypedStringValue;
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

use function count;
use function trim;

final class Interpreter
{
    private static None $noneJump;
    private readonly JumpStack $jumpStack;
    private readonly DeferredStack $deferredStack;
    private readonly ScopeResolver $scopeResolver;
    private readonly InvokableCallList $initializers;
    private readonly TypeResolver $typeResolver;
    private bool $constContext = false;
    private int $switchContext = 0;
    private ?FuncValue $entryPoint = null;
    private ?Ast $ast = null;

    public function __construct(
        private readonly string $source,
        private readonly ?string $filename,
        private readonly Argv $argv,
        private readonly ErrorHandler $errorHandler,
        private readonly StreamProvider $streams,
        private readonly FuncTypeValidator $entryPointValidator,
        private readonly FuncTypeValidator $initValidator,
        private readonly ImportHandler $importHandler,
        private readonly Iota $iota,
        private PanicPointer $panicPointer,
        private Environment $env,
        private readonly ?Debugger $debugger,
    ) {
        self::init();
        $this->jumpStack = new JumpStack();
        $this->deferredStack = new DeferredStack();
        $this->scopeResolver = new ScopeResolver();
        $this->initializers = new InvokableCallList();
        $this->typeResolver = new TypeResolver(
            $this->scopeResolver,
            $this->tryEvalConstExpr(...),
            $this->env,
        );
    }

    /**
     * Creates an interpreter instance
     *
     * @param string|null $source Source code to execute
     * @param list<GoValue> $argv Command line arguments
     * @param BuiltinProvider|null $builtin Builtin package provider
     * @param ErrorHandler|null $errorHandler Error handler
     * @param StreamProvider $streams Stream provider of stdout, stderr, stdin
     * @param FuncTypeValidator $entryPointValidator Validator for entry point function
     * @param FuncTypeValidator $initValidator Validator for package initializer functions
     * @param EnvVarSet $envVars Environment variables
     * @param string|null $filename Filename of the source code
     * @param bool $toplevel Whether the source is a top level code or not
     * @param bool $debug Whether to enable debug mode or not
     * @param Debugger|null $debugger Debugger, if $debug is set to false, this is ignored
     * @param list<string> $customFileExtensions Custom file extensions to include when importing
     *
     * @return self
     */
    public static function create(
        ?string $source,
        array $argv = [],
        ?BuiltinProvider $builtin = null,
        ?ErrorHandler $errorHandler = null,
        StreamProvider $streams = new StdStreamProvider(),
        FuncTypeValidator $entryPointValidator = new ZeroArityValidator(
            ENTRY_POINT_FUNC,
            ENTRY_POINT_PACKAGE,
        ),
        FuncTypeValidator $initValidator = new ZeroArityValidator(INITIALIZER_FUNC),
        EnvVarSet $envVars = new EnvVarSet(),
        ?string $filename = null,
        bool $toplevel = false,
        bool $debug = false,
        ?Debugger $debugger = null,
        array $customFileExtensions = [],
    ): self {
        self::init();

        $errorHandler ??= new StreamWriter($streams->stderr());
        $builtin ??= new StdBuiltinProvider($streams->stderr());
        $importHandler = new ImportHandler($envVars, $customFileExtensions);

        if ($debug) {
            $debugger ??= new CallStackCollectorDebugger();
        } else {
            $debugger = null;
        }

        if ($source === null) {
            if ($filename === null) {
                throw new InternalError('source or filename must be provided');
            }

            $source = $importHandler->importFromFile($filename);
        }

        return new self(
            source: $toplevel ? self::wrapSource($source, $entryPointValidator) : $source,
            filename: $filename,
            argv: (new ArgvBuilder($argv))->build(),
            errorHandler: $errorHandler,
            streams: $streams,
            entryPointValidator: $entryPointValidator,
            initValidator: $initValidator,
            importHandler: $importHandler,
            iota: $builtin->iota(),
            panicPointer: $builtin->panicPointer(),
            env: Environment::fromEnclosing($builtin->env()),
            debugger: $debugger,
        );
    }

    /**
     * @throws InternalError Unexpected error during execution
     * @return RuntimeResult Result of execution
     */
    public function run(): RuntimeResult
    {
        $resultBuilder = new RuntimeResultBuilder();
        $resultBuilder->setDebugger($this->debugger);

        try {
            $ast = $this->parseSourceToAst($this->source);
            $this->setAst($ast);
            $this->evalDeclsInOrder();
            $this->checkEntryPoint();
            $call = new InvokableCall($this->entryPoint, Argv::fromEmpty());
            $this->callFunc($call);
        } catch (RuntimeError|PanicError $error) {
            $this->errorHandler->onError($error);

            return $resultBuilder
                ->setExitCode(ExitCode::Failure)
                ->setError($error)
                ->build();
        } catch (AbortExecutionError) {
            return $resultBuilder
                ->setExitCode(ExitCode::Failure)
                ->build();
        }

        return $resultBuilder
            ->setExitCode(ExitCode::Success)
            ->build();
    }

    /**
     * @psalm-assert !null $this->entryPoint
     */
    private function checkEntryPoint(): void
    {
        if ($this->entryPoint !== null) {
            return;
        }

        if ($this->scopeResolver->entryPointPackage === $this->entryPointValidator->getPackageName()) {
            throw RuntimeError::noEntryPointFunction(
                $this->entryPointValidator->getFuncName(),
                $this->entryPointValidator->getPackageName(),
            );
        }

        throw RuntimeError::notEntryPointPackage(
            $this->scopeResolver->entryPointPackage,
            $this->entryPointValidator->getPackageName(),
        );
    }

    private function evalDeclsInOrder(): void
    {
        $this->assertAstIsSet();

        foreach ($this->ast->imports as $import) {
            $this->evalImportDeclStmt($import);
        }

        $mapping = [
            TypeDecl::class => [],
            ConstDecl::class => [],
            VarDecl::class => [],
            FuncDecl::class => [],
            MethodDecl::class => [],
        ];

        foreach ($this->ast->decls as $decl) {
            if (!isset($mapping[$decl::class])) {
                throw RuntimeError::nonDeclarationOnTopLevel();
            }

            $mapping[$decl::class][] = $decl;
        }

        $this->scopeResolver->enterPackageScope();

        foreach ($mapping as $decls) {
            foreach ($decls as $decl) {
                /** @psalm-suppress all */
                (match ($decl::class) {
                    TypeDecl::class => $this->evalTypeDeclStmt(...),
                    ConstDecl::class => $this->evalConstDeclStmt(...),
                    VarDecl::class => $this->evalVarDeclStmt(...),
                    FuncDecl::class => $this->evalFuncDeclStmt(...),
                    MethodDecl::class => $this->evalMethodDeclStmt(...),
                })($decl);
            }
        }

        foreach ($this->initializers->get() as $initializer) {
            $this->callFunc($initializer);
        }

        $this->initializers->empty();
        $this->scopeResolver->exitPackageScope();
    }

    private function evalImportDeclStmt(ImportDecl $decl): void
    {
        $this->assertAstIsSet();
        $mainAst = $this->ast;

        foreach (iter_spec($decl->spec) as $spec) {
            /** @var UntypedStringValue $path */
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
        $this->scopeResolver->entryPointPackage = $ast->package->identifier->name;
        $this->scopeResolver->currentPackage = $ast->package->identifier->name;
    }

    private function parseSourceToAst(string $source): Ast
    {
        $parser = new Parser($source, $this->filename);

        /** @var Ast $ast */
        $ast = $parser->parse();

        if ($parser->hasErrors()) {
            foreach ($parser->getErrors() as $error) {
                $error = ParserError::fromInternalParserError($error);
                $this->errorHandler->onError($error);
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
                $type = $this->typeResolver->resolve($spec->type);
            }

            if ($type !== null && !$type instanceof NamedType) {
                throw RuntimeError::invalidConstantType($type);
            }

            if ($spec->initList !== null && !empty($spec->initList->exprs)) {
                $initExprs = $spec->initList->exprs;
            }

            if (count($initExprs) > count($spec->identList->idents)) {
                throw RuntimeError::extraInitExpr();
            }

            foreach ($spec->identList->idents as $i => $ident) {
                $value = isset($initExprs[$i])
                    ? $this->tryEvalConstExpr($initExprs[$i])
                    : null;

                if (!$value instanceof AddressableValue) {
                    throw $value === null
                        ? RuntimeError::uninitialisedConstant($ident->name)
                        : InternalError::unexpectedValue($value);
                }

                $this->checkNonDeclarableNames($ident->name);
                $constType = $type === null
                    ? $value->type()
                    : reify_untyped($type);

                $this->env->defineConst(
                    $ident->name,
                    $value->copy(),
                    $constType,
                    $this->scopeResolver->resolveDefinitionScope(),
                );
            }
        }

        $this->constContext = false;

        return self::$noneJump;
    }

    private function evalVarDeclStmt(VarDecl $decl): None
    {
        foreach (iter_spec($decl->spec) as $spec) {
            $type = null;
            if ($spec->type !== null) {
                $type = $this->typeResolver->resolve($spec->type);
            }

            $values = [];
            $identsLen = count($spec->identList->idents);

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

        return self::$noneJump;
    }

    private function evalTypeDeclStmt(TypeDecl $decl): None
    {
        foreach (iter_spec($decl->spec) as $spec) {
            match (true) {
                $spec->value instanceof AliasDecl => $this->evalAliasDeclStmt($spec->value),
                $spec->value instanceof TypeDef => $this->evalTypeDefStmt($spec->value),
            };
        }

        return self::$noneJump;
    }

    private function evalAliasDeclStmt(AliasDecl $decl): void
    {
        $alias = $decl->ident->name;
        $typeValue = new TypeValue($this->typeResolver->resolve($decl->type));

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
        $type = $this->typeResolver->resolve($stmt->type);
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

        $receiver = Receiver::fromParams(
            $this->typeResolver->resolveParamsFromAstParams($decl->receiver),
        );

        $funcValue = $this->constructFuncValue($decl->signature, $decl->body, $receiver);
        $currentPackage = $this->scopeResolver->currentPackage;

        $receiverType = $receiver->getType();
        if (!$receiverType->isLocal($currentPackage)) {
            throw RuntimeError::methodOnNonLocalType($receiver->type);
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
            $this->initializers->add(new InvokableCall($funcValue, Argv::fromEmpty()));

            return;
        }

        $this->env->defineFunc($decl->name->name, $funcValue, $currentPackage);
    }

    private function constructFuncValue(
        AstSignature $signature,
        BlockStmt $blockStmt,
        ?Receiver $receiver = null,
    ): FuncValue {
        $type = $this->typeResolver->resolveTypeFromAstSignature($signature);

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

        return self::$noneJump;
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
        $exprLen = count($expr->args->exprs);
        $nValuedContext = VALUE_CONTEXT_SINGLE;
        $argvBuilder = new ArgvBuilder();
        $startFrom = 0;

        if (
            $func instanceof BuiltinFuncValue
            && $func->func instanceof ExpectsTypeAsFirstArg
            && isset($expr->args->exprs[0])
        ) {
            if (!$expr->args->exprs[0] instanceof AstType) {
                throw InternalError::unexpectedValue($expr->args->exprs[0]);
            }

            $argvBuilder->add(new TypeValue($this->typeResolver->resolve($expr->args->exprs[0])));
            ++$startFrom;
        }

        for ($i = $startFrom; $i < $exprLen; ++$i) {
            $arg = $this->evalExpr($expr->args->exprs[$i]);

            if ($arg instanceof TupleValue) {
                if ($exprLen !== VALUE_CONTEXT_SINGLE) {
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
            if ($nValuedContext > VALUE_CONTEXT_SINGLE) {
                throw RuntimeError::cannotSplatMultipleValuedReturn($nValuedContext);
            }

            $argvBuilder->markUnpacked($func);
        }

        $call = new InvokableCall($func, $argvBuilder->build());
        $call->setPosition($expr->lParen->pos);

        return $call;
    }

    private function evalCallExpr(CallExpr $expr): GoValue
    {
        return $this->callFunc(
            $this->evalCallExprWithoutCall($expr),
        );
    }

    private function callFunc(InvokableCall $fn): GoValue
    {
        $this->jumpStack->push(new JumpHandler());
        $this->deferredStack->newContext();
        $this->debugger?->addStackTrace($fn);

        try {
            $value = $fn();
            $this->releaseDeferredStack();
            $this->debugger?->releaseLastStackTrace();

            return $value;
        } catch (PanicError $panic) {
            $this->panicPointer->set($panic);
            $this->releaseDeferredStack();

            if ($this->panicPointer->pointsTo() === null && ($recover = $fn->tryRecover()) !== null) {
                return $recover;
            }

            throw $panic;
        } finally {
            $this->jumpStack->pop();
        }
    }

    private function evalIndexExpr(IndexExpr $expr): GoValue
    {
        $sequence = $this->evalExpr($expr->expr);
        $sequence = try_unwind($sequence);

        if (!$sequence instanceof Sequence) {
            throw RuntimeError::cannotIndex($sequence->type());
        }

        $index = $this->evalExpr($expr->index);

        return $sequence->get($index);
    }

    private function evalSliceExpr(SimpleSliceExpr|FullSliceExpr $expr): GoValue&Sliceable
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

    private function evalTypeAssertionExpr(TypeAssertionExpr $expr): GoValue
    {
        $value = $this->evalExpr($expr->expr);
        $type = $this->typeResolver->resolve($expr->type);

        if (!$value instanceof InterfaceValue) {
            throw RuntimeError::nonInterfaceAssertion($value);
        }

        if ($value->isNil() || !$type->isCompatible($value->value->type())) {
            throw PanicError::interfaceConversion($value, $type);
        }

        return $value->value;
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
        return self::$noneJump;
    }

    private function evalBreakStmt(BreakStmt $stmt): BreakJump
    {
        return new BreakJump($stmt->label?->name);
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
        return new ContinueJump($stmt->label?->name);
    }

    private function evalExprStmt(ExprStmt $stmt): None
    {
        $this->evalExpr($stmt->expr);

        return self::$noneJump;
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
            $stmtJump = self::$noneJump;
            $len = count($stmtList->stmts);
            $gotoIndex = 0;

            for ($i = 0; $i < $len; ++$i) {
                $stmt = $stmtList->stmts[$i];

                if ($jump->isSeeking()) {
                    $stmt = $jump->tryFindLabel($stmt, $gotoIndex > $i);

                    if ($stmt === null || $jump->resetStatus()) {
                        continue;
                    }
                }

                $stmtJump = $this->evalStmt($stmt);

                if ($stmtJump instanceof GotoJump || $stmtJump instanceof BreakJump) {
                    if ($stmtJump->label === null) {
                        break;
                    }

                    $jump->startSeeking($stmtJump->label);

                    if ($jump->isSameContext($stmtList)) {
                        /** @psalm-suppress LoopInvalidation */
                        [$i, $gotoIndex] = [-1, $i];
                        continue;
                    }

                    return $stmtJump;
                }

                if ($stmtJump instanceof ReturnJump || $stmtJump instanceof ContinueJump) {
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
        [$prevEnv, $this->env] = [$this->env, $env ?? Environment::fromEnclosing($this->env)];

        try {
            return $code();
        } finally {
            $this->env = $prevEnv;
        }
    }

    private function evalReturnStmt(ReturnStmt $stmt): ReturnJump
    {
        if ($stmt->exprList === null || empty($stmt->exprList->exprs)) {
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

        if (count($values) === ReturnJump::LEN_SINGLE) {
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

        return self::$noneJump;
    }

    private function evalIfStmt(IfStmt $stmt): StmtJump
    {
        return $this->evalWithEnvWrap(null, function () use ($stmt): StmtJump {
            if ($stmt->init !== null) {
                $this->evalStmt($stmt->init);
            }

            $condition = $this->evalExpr($stmt->condition);

            if (self::isTrue($condition, $stmt)) {
                return $this->evalBlockStmt($stmt->ifBody);
            }

            if ($stmt->elseBody !== null) {
                return $this->evalStmt($stmt->elseBody);
            }

            return self::$noneJump;
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

            while ($condition === null || self::isTrue($this->evalExpr($condition), $stmt)) {
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
                        if ($stmtJump->label === null) {
                            return self::$noneJump;
                        }
                        $this->jumpStack->peek()->setStatus(JumpStatus::Break);
                        return $stmtJump;
                    case $stmtJump instanceof ReturnJump || $stmtJump instanceof GotoJump:
                        return $stmtJump;
                    default:
                        throw InternalError::unreachable($stmtJump);
                }

                if ($post !== null) {
                    $this->evalStmt($post);
                }
            }

            return self::$noneJump;
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

            $stmtJump = self::$noneJump;
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
            $stmtJump = self::$noneJump;
        }

        return $stmtJump;
    }

    private function evalSwitchWithFallthrough(ExprSwitchStmt|TypeSwitchStmt $stmt, int $fromCase): StmtJump
    {
        $stmtJump = self::$noneJump;

        for (
            $i = $fromCase,
            $caseClausesLen = count($stmt->caseClauses);
            $i < $caseClausesLen;
            $i++
        ) {
            $stmtJump = $this->evalStmtList($stmt->caseClauses[$i]->stmtList);

            if ($stmtJump instanceof FallthroughJump) {
                if ($i === $caseClausesLen - 1) {
                    throw RuntimeError::fallthroughFinalCase();
                }

                $stmtJump = self::$noneJump;

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

        [$keyVar, $valVar] = match (count($iterVars)) {
            VALUE_CONTEXT_ZERO => [null, null],
            VALUE_CONTEXT_SINGLE => [$iterVars[0], null],
            VALUE_CONTEXT_DOUBLE => $iterVars,
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
                    return self::$noneJump;
                case $stmtJump instanceof ReturnJump || $stmtJump instanceof GotoJump:
                    return $stmtJump;
                default:
                    throw InternalError::unreachable($stmtJump);
            }
        }

        return self::$noneJump;
    }

    private function evalIncDecStmt(IncDecStmt $stmt): None
    {
        $this
            ->evalExpr($stmt->lhs)
            ->mutate(
                Operator::fromAst($stmt->op),
                new UntypedIntValue(1),
            );

        return self::$noneJump;
    }

    private function evalAssignmentStmt(AssignmentStmt $stmt): None
    {
        $op = Operator::fromAst($stmt->op);

        if (!$op->isAssignment()) {
            throw RuntimeError::expectedAssignmentOperator($op);
        }

        $lhsLen = count($stmt->lhs->exprs);
        $rhs = $this->collectValuesFromExprList($stmt->rhs, $lhsLen);

        if ($lhsLen === VALUE_CONTEXT_SINGLE) {
            $this->evalLhsExpr($stmt->lhs->exprs[0])->mutate($op, $rhs[0]);

            return self::$noneJump;
        }

        $lhs = [];
        $lhsCopies = [];
        foreach ($stmt->lhs->exprs as $i => $expr) {
            $lhsVal = $this->evalLhsExpr($expr);
            $lhsValCopy = $lhsVal->copy();
            $lhsValCopy->mutate($op, $rhs[$i]);
            $lhs[] = $lhsVal;
            $lhsCopies[] = $lhsValCopy;
        }

        foreach ($lhs as $i => $lhsVal) {
            $lhsVal->mutate(Operator::Eq, $lhsCopies[$i]);
        }

        return self::$noneJump;
    }

    private function evalShortVarDeclStmt(ShortVarDecl $stmt): None
    {
        $len = count($stmt->identList->idents);
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

        return self::$noneJump;
    }

    /**
     * @return array<GoValue>
     */
    private function collectValuesFromExprList(ExprList $exprList, int $expectedLen): array
    {
        $value = $this->evalExpr($exprList->exprs[0]);
        $exprLen = count($exprList->exprs);

        if ($value instanceof TupleValue) {
            if ($exprLen !== VALUE_CONTEXT_SINGLE) {
                throw RuntimeError::multipleValueInSingleContext($value);
            }

            if ($value->len !== $expectedLen) {
                throw RuntimeError::assignmentMismatch($expectedLen, $value->len);
            }

            return $value->values;
        }

        if (
            $expectedLen === VALUE_CONTEXT_DOUBLE
            && $exprLen === VALUE_CONTEXT_SINGLE
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
            $expr instanceof TypeAssertionExpr => $this->evalTypeAssertionExpr($expr),
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

        $type = $this->typeResolver->resolve($lit->type, true);

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
        return UntypedIntValue::fromRune(trim($lit->rune, '\''));
    }

    private function evalStringLit(StringLit $lit): UntypedStringValue
    {
        return new UntypedStringValue(trim($lit->str, '"'));
    }

    private function evalRawStringLit(RawStringLit $lit): UntypedStringValue
    {
        return new UntypedStringValue(trim($lit->str, '`'));
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
        if ($expr->expr instanceof Ident && $this->env->isNamespaceDefined($expr->expr->name)) {
            return $this->env->get(
                $expr->selector->name,
                $expr->expr->name,
                implicit: false,
            )->unwrap();
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
            $value = try_unwind($value);

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
            $this->scopeResolver->currentPackage,
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

    private static function isTrue(GoValue $value, Stmt $context): bool
    {
        if (!$value instanceof BoolValue) {
            throw RuntimeError::nonBooleanCondition($context);
        }

        return $value->isTrue();
    }

    private function defineVar(string $name, GoValue $value, ?GoType $type = null): void
    {
        $this->checkNonDeclarableNames($name);
        $initValue = null;

        if ($value instanceof UntypedNilValue && $type instanceof RefType) {
            $initValue = $type->zeroValue();
        } elseif ($type instanceof InterfaceType) {
            $initValue = $type->zeroValue();
            $initValue->mutate(Operator::Eq, $value);
        } else {
            /** @var AddressableValue $value */
            $initValue = $value->copy();
        }

        $this->env->defineVar(
            $name,
            $initValue,
            reify_untyped($type ?? $value->type()),
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

    private static function wrapSource(string $source, FuncTypeValidator $entryPointValidator): string
    {
        return <<<GO
        package {$entryPointValidator->getPackageName()}
        
        func {$entryPointValidator->getFuncName()}() {
            {$source}
        }
        GO;
    }

    private static function init(): void
    {
        static $init = false;

        if ($init) {
            return;
        }

        $init = true;
        self::$noneJump = new None();
    }

    /**
     * @psalm-assert !null $this->ast
     */
    private function assertAstIsSet(): void
    {
        if ($this->ast !== null) {
            return;
        }

        throw new InternalError('AST is not set');
    }
}
