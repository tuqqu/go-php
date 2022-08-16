<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Error\OperationError;

trait SealableTrait
{
    protected bool $sealed = false;

    public function seal(): void
    {
        $this->sealed = true;
    }

    public function isSealed(): bool
    {
        return $this->sealed;
    }

    final protected function onMutate(): void
    {
        if ($this->sealed) {
            throw OperationError::cannotAssign($this);
        }
    }
}
