<?php

declare(strict_types=1);

namespace GoPhp;

use GoParser\Ast\Stmt\BlockStmt as BlockContext;
use GoPhp\Error\DefinitionError;

final class LabelJump
{
    private ?string $seekLabel = null;
    private ?BlockContext $context = null;
    private array $metLabels = [];

    public function setContext(BlockContext $context): void
    {
        $this->context ??= $context;
    }

    public function stopSeeking(): void
    {
        $this->seekLabel = null;
    }

    public function isSeeking(): bool
    {
        return $this->seekLabel !== null;
    }

    public function startSeeking(string $label): void
    {
        $this->seekLabel = $label;
    }

    public function addLabel(string $label): void
    {
        if ($this->hasMet($label)) {
            throw DefinitionError::labelAlreadyDefined($label);
        }

        $this->metLabels[] = $label;
    }

    public function isSought(string $label): bool
    {
        return $label === $this->seekLabel;
    }

    public function hasMet(string $label): bool
    {
        return \in_array($label, $this->metLabels, true);
    }

    public function isSameContext(BlockContext $context): bool
    {
        return $this->context === $context;
    }
}
