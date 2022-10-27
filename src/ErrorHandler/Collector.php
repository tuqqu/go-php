<?php

declare(strict_types=1);

namespace GoPhp\ErrorHandler;

/**
 * Error handler that collects given errors into an array.
 */
final class Collector implements ErrorHandler
{
    /** @var list<string> */
    private array $errors = [];

    public function onError(string|\Stringable $error): void
    {
        $this->errors[] = (string) $error;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
