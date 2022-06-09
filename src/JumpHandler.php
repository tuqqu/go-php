<?php

declare(strict_types=1);

namespace GoPhp;

use GoParser\Ast\Stmt\Decl;
use GoParser\Ast\Stmt\LabeledStmt;
use GoParser\Ast\Stmt\Stmt;
use GoParser\Ast\Stmt\TypeDecl;
use GoPhp\Error\DefinitionError;
use GoPhp\Error\InternalError;
use GoPhp\Error\ProgramError;

final class JumpHandler
{
    private ?string $soughtForLabel = null;
    private ?object $context = null;
    private array $metLabels = [];

    public function setContext(object $context): void
    {
        $this->context ??= $context;
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
            throw DefinitionError::labelAlreadyDefined($label->label->name);
        }

        $this->metLabels[$label->label->name] = \spl_object_id($label);
    }

    public function isSameContext(object $context): bool
    {
        return $this->context === $context;
    }

    public function getLabel(): string
    {
        return $this->soughtForLabel ?? throw new InternalError('label was not found');
    }

    public function tryFindLabel(Stmt $stmt, bool $ignoreDecl): ?Stmt
    {
        if (
            !$ignoreDecl
            && $stmt instanceof Decl
            && !$stmt instanceof TypeDecl
        ) {
            throw ProgramError::jumpBeforeDecl();
        }

        if (!$stmt instanceof LabeledStmt) {
            return null;
        }

        $this->addLabel($stmt);

        if ($stmt->label->name === $this->soughtForLabel) {
            $this->stopSeeking();

            return $stmt->stmt;
        }

        return null;
    }

    private function stopSeeking(): void
    {
        $this->soughtForLabel = null;
    }

    private function hasMet(LabeledStmt $label): bool
    {
        return isset($this->metLabels[$label->label->name])
            && $this->metLabels[$label->label->name] !== \spl_object_id($label);
    }
}
