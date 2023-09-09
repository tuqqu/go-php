<?php

declare(strict_types=1);

namespace GoPhp\ErrorHandler;

use GoPhp\Error\GoError;

/**
 * Error handler that collects errors into an array.
 */
final class Collector implements ErrorHandler
{
    /** @var list<GoError> */
    private array $errors = [];

    public function onError(GoError $error): void
    {
        $this->errors[] = $error;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
