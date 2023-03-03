<?php

declare(strict_types=1);

namespace GoPhp;

use GoParser\Ast\Stmt\Decl;
use GoParser\Ast\Stmt\ForStmt;
use GoParser\Ast\Stmt\LabeledStmt;
use GoParser\Ast\Stmt\Stmt;
use GoParser\Ast\Stmt\SwitchStmt;
use GoParser\Ast\Stmt\TypeDecl;
use GoPhp\Error\RuntimeError;
use GoPhp\Error\InternalError;

final class JumpHandler
{
    private const DEFAULT_STATUS = JumpStatus::Goto;

    /** @var array<LabeledStmt> */
    private array $labeledStmts = [];
    private ?string $soughtForLabel = null;
    private ?object $context = null;
    private JumpStatus $status = self::DEFAULT_STATUS;

    public function setContext(object $context): void
    {
        $this->context ??= $context;
    }

    public function setStatus(JumpStatus $status): void
    {
        $this->status = $status;
    }

    public function resetStatus(): bool
    {
        try {
            return $this->status->isLoopJump();
        } finally {
            $this->status = self::DEFAULT_STATUS;
        }
    }

    public function isSeeking(): bool
    {
        return $this->soughtForLabel !== null;
    }

    public function startSeeking(string $label): void
    {
        $this->soughtForLabel = $label;
    }

    public function addLabel(LabeledStmt $label): void
    {
        if ($this->hasMet($label)) {
            throw RuntimeError::labelAlreadyDefined($label->label->name);
        }

        $this->labeledStmts[$label->label->name] = $label;
    }

    public function isSameContext(object $context): bool
    {
        return $this->context === $context;
    }

    public function getLabel(): string
    {
        return $this->soughtForLabel ?? throw InternalError::unreachable('label not set');
    }

    public function tryFindLabel(Stmt $stmt, bool $ignoreDecl): ?Stmt
    {
        if (
            !$ignoreDecl
            && $stmt instanceof Decl
            && !$stmt instanceof TypeDecl
        ) {
            throw RuntimeError::jumpBeforeDecl();
        }

        if (!$stmt instanceof LabeledStmt) {
            return null;
        }

        $this->addLabel($stmt);

        if ($stmt->label->name === $this->soughtForLabel) {
            $this->stopSeeking();

            if ($this->status->isLoopJump()) {
                if (!self::isBreakableStmt($stmt)) {
                    throw RuntimeError::invalidLabel($stmt->label->name, $this->status);
                }
            }

            return $stmt->stmt;
        }

        if ($this->status->isLoopJump()) {
            throw RuntimeError::undefinedLoopLabel($stmt->label->name, $this->status);
        }

        return null;
    }

    private function stopSeeking(): void
    {
        $this->soughtForLabel = null;
    }

    private function hasMet(LabeledStmt $label): bool
    {
        return isset($this->labeledStmts[$label->label->name]) && $this->labeledStmts[$label->label->name] !== $label;
    }

    private static function isBreakableStmt(LabeledStmt $label): bool
    {
        return match (true) {
            $label->stmt instanceof ForStmt,
            $label->stmt instanceof SwitchStmt => true,
            default => false,
        };
    }
}
