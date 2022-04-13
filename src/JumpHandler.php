<?php

declare(strict_types=1);

namespace GoPhp;

use GoParser\Ast\Stmt\BlockStmt as BlockContext;
use GoParser\Ast\Stmt\Decl;
use GoParser\Ast\Stmt\LabeledStmt;
use GoParser\Ast\Stmt\Stmt;
use GoParser\Ast\Stmt\TypeDecl;
use GoPhp\Error\DefinitionError;
use GoPhp\Error\InternalError;

final class JumpHandler
{
    private ?string $seekLabel = null;
    private ?BlockContext $context = null;
    private array $metLabels = [];
    private bool $jumped = false;

    public function setContext(BlockContext $context): void
    {
        $this->context ??= $context;
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
        if (!$this->jumped && $this->hasMet($label)) {
            throw DefinitionError::labelAlreadyDefined($label);
        }

        if ($this->jumped) {
            $this->jumped = false;
        }

        $this->metLabels[] = $label;
    }

    public function isSameContext(BlockContext $context): bool
    {
        $same = $this->context === $context;

        if ($same) {
            $this->jumped = true;
        }

        return $same;
    }

    public function getLabel(): string
    {
        return $this->seekLabel;
    }

    public function tryFindLabel(Stmt $stmt): ?Stmt
    {
        if ($stmt instanceof Decl && !$stmt instanceof TypeDecl) {
            throw new InternalError('goto jumps over declaration');
        }

        if (!$stmt instanceof LabeledStmt) {
            return null;
        }

        $this->addLabel($stmt->label->name);

        if ($stmt->label->name === $this->seekLabel) {
            $this->stopSeeking();

            return $stmt->stmt;
        }

        return null;
    }

    private function stopSeeking(): void
    {
        $this->seekLabel = null;
    }

    private function hasMet(string $label): bool
    {
        return \in_array($label, $this->metLabels, true);
    }
}
