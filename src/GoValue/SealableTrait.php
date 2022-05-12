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

    final protected function onMutate(): void
    {
        if ($this->sealed) {
            throw OperationError::cannotAssignToConst($this);
        }
    }
}
