<?php

declare(strict_types=1);

namespace GoPhp;

use Closure;
use GoParser\Ast\EmbeddedFieldDecl;
use GoParser\Ast\Expr\ArrayType as AstArrayType;
use GoParser\Ast\Expr\Expr;
use GoParser\Ast\Expr\FuncType as AstFuncType;
use GoParser\Ast\Expr\InterfaceType as AstInterfaceType;
use GoParser\Ast\Expr\MapType as AstMapType;
use GoParser\Ast\Expr\ParenType;
use GoParser\Ast\Expr\PointerType as AstPointerType;
use GoParser\Ast\Expr\QualifiedTypeName;
use GoParser\Ast\Expr\SingleTypeName;
use GoParser\Ast\Expr\SliceType as AstSliceType;
use GoParser\Ast\Expr\StructType as AstStructType;
use GoParser\Ast\Expr\Type as AstType;
use GoParser\Ast\Expr\TypeTerm;
use GoParser\Ast\Keyword;
use GoParser\Ast\MethodElem;
use GoParser\Ast\Params as AstParams;
use GoParser\Ast\Signature as AstSignature;
use GoParser\Lexer\Token;
use GoPhp\Env\Environment;
use GoPhp\Error\InternalError;
use GoPhp\Error\RuntimeError;
use GoPhp\GoType\ArrayType;
use GoPhp\GoType\FuncType;
use GoPhp\GoType\GoType;
use GoPhp\GoType\InterfaceType;
use GoPhp\GoType\MapType;
use GoPhp\GoType\PointerType;
use GoPhp\GoType\SliceType;
use GoPhp\GoType\StructType;
use GoPhp\GoValue\Func\Param;
use GoPhp\GoValue\Func\Params;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Int\IntNumber;
use GoPhp\GoValue\TypeValue;

final class TypeResolver
{
    /**
     * @param Closure(Expr): ?GoValue $constEvaluator
     */
    public function __construct(
        private readonly ScopeResolver $scopeResolver,
        private readonly Closure $constEvaluator,
        private Environment &$envRef,
    ) {}

    public function resolve(AstType $type, bool $composite = false): GoType
    {
        return match (true) {
            $type instanceof SingleTypeName => $this->resolveTypeFromSingleName($type),
            $type instanceof QualifiedTypeName => $this->resolveTypeFromQualifiedName($type),
            $type instanceof ParenType => $this->resolveTypeFromParenType($type),
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

    public function resolveParamsFromAstParams(AstParams $astParams): Params
    {
        $params = [];
        foreach ($astParams->paramList as $paramDecl) {
            if ($paramDecl->identList === null) {
                $params[] = new Param(
                    $this->resolve($paramDecl->type),
                    null,
                    $paramDecl->ellipsis !== null,
                );
                continue;
            }

            foreach ($paramDecl->identList->idents as $ident) {
                $params[] = new Param(
                    $this->resolve($paramDecl->type),
                    $ident->name,
                    $paramDecl->ellipsis !== null,
                );
            }
        }

        return new Params($params);
    }

    public function resolveTypeFromAstSignature(AstSignature $signature): FuncType
    {
        return new FuncType(...$this->resolveParamsFromAstSignature($signature));
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

    private function resolveTypeFromParenType(ParenType $type): GoType
    {
        if ($type->type instanceof Keyword) {
            throw RuntimeError::typeOutsideTypeSwitch();
        }

        return $this->resolve($type->type);
    }

    private function resolveArrayType(AstArrayType $arrayType, bool $composite): ArrayType
    {
        $elemType = $this->resolve($arrayType->elemType, $composite);

        if ($arrayType->len instanceof Expr) {
            $len = ($this->constEvaluator)($arrayType->len) ?? throw RuntimeError::invalidArrayLen();

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
        return new SliceType($this->resolve($sliceType->elemType, $composite));
    }

    private function resolveMapType(AstMapType $mapType, bool $composite): MapType
    {
        return new MapType(
            $this->resolve($mapType->keyType, $composite),
            $this->resolve($mapType->elemType, $composite),
        );
    }

    private function resolvePointerType(AstPointerType $pointerType, bool $composite): PointerType
    {
        return new PointerType(
            $this->resolve($pointerType->type, $composite),
        );
    }

    private function resolveStructType(AstStructType $structType, bool $composite): StructType
    {
        /** @var array<string, GoType> $fields */
        $fields = [];
        $promotedNames = [];

        foreach ($structType->fieldDecls as $fieldDecl) {
            $type = $this->resolve($fieldDecl->type, $composite);

            if ($fieldDecl instanceof EmbeddedFieldDecl) {
                $name = self::getNameForEmbeddedField($fieldDecl);

                if (isset($fields[$name])) {
                    throw RuntimeError::redeclaredName($name);
                }

                $fields[$name] = $type;

                if (try_unwind($type) instanceof StructType) {
                    $promotedNames[] = $name;
                }

                continue;
            }

            foreach ($fieldDecl->identList->idents as $ident) {
                if (isset($fields[$ident->name])) {
                    throw RuntimeError::redeclaredName($ident->name);
                }

                $fields[$ident->name] = $type;
            }
        }

        return new StructType($fields, $promotedNames);
    }

    private function resolveInterfaceType(AstInterfaceType $interfaceType, bool $composite): InterfaceType
    {
        // fixme use composite param
        $methods = [];
        foreach ($interfaceType->items as $item) {
            switch (true) {
                case $item instanceof TypeTerm:
                    throw InternalError::unimplemented();
                case $item instanceof MethodElem:
                    if (isset($methods[$item->methodName->name])) {
                        throw RuntimeError::duplicateMethod($item->methodName->name);
                    }

                    $methods[$item->methodName->name] = $this->resolveTypeFromAstSignature($item->signature);
                    break;
                default:
                    throw InternalError::unreachable($item);
            }
        }

        return InterfaceType::withMethods($methods, $this->envRef);
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
                $signature->result instanceof AstType => Params::fromParam(new Param($this->resolve($signature->result))),
                $signature->result instanceof AstParams => $this->resolveParamsFromAstParams($signature->result),
            },
        ];
    }

    private function getTypeFromEnv(string $name, string $namespace): GoType
    {
        $value = $this->envRef->get($name, $namespace)->unwrap();

        if (!$value instanceof TypeValue) {
            throw RuntimeError::valueIsNotType($value);
        }

        return $value->unwrap();
    }

    private static function getNameForEmbeddedField(EmbeddedFieldDecl $fieldDecl): string
    {
        $type = $fieldDecl->type;

        if ($type instanceof AstPointerType) {
            $type = $type->type;
        }

        if ($type instanceof QualifiedTypeName) {
            $type = $type->typeName;
        }

        /** @var SingleTypeName $type */
        return $type->name->name;
    }
}
