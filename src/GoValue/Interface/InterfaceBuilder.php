<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Interface;

use GoParser\Ast\KeyedElement;
use GoPhp\CompositeValueBuilder;
use GoPhp\Error\InternalError;
use GoPhp\GoType\FuncType;
use GoPhp\GoType\InterfaceType;

final class InterfaceBuilder implements CompositeValueBuilder
{
    /** @var array<string, FuncType> */
    private array $methods = [];

    private function __construct(
        private readonly InterfaceType $type,
    ) {}

    public static function fromType(InterfaceType $type): self
    {
        return new self($type);
    }

    public function push(KeyedElement $element, callable $evaluator): void
    {
        throw InternalError::unimplemented();
    }

    public function build(): InterfaceValue
    {
        throw InternalError::unimplemented();
    }
}
