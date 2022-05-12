<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Error\OperationError;

trait ConstantableTrait
{
    private bool $constant = false;

    public function makeConst(): void
    {
        $this->constant = true;
    }

    public function onMutate(): void
    {
        if ($this->constant) {
            throw OperationError::cannotAssignToConst($this);
        }
    }
}
