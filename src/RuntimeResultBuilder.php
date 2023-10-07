<?php

declare(strict_types=1);

namespace GoPhp;

use GoPhp\Error\GoError;
use GoPhp\Error\InternalError;
use GoPhp\GoValue\GoValue;

final class RuntimeResultBuilder
{
    private ?GoValue $result = null;
    private ?Debugger $debugger = null;
    private ?GoError $error = null;
    private ?ExitCode $exitCode = null;

    public function setExitCode(ExitCode $exitCode): void
    {
        $this->exitCode = $exitCode;
    }

    public function setResult(GoValue $result): void
    {
        $this->result = $result;
    }

    public function setDebugger(Debugger $debugger): void
    {
        $this->debugger = $debugger;
    }

    public function setError(GoError $error): void
    {
        $this->error = $error;
    }

    public function build(): RuntimeResult
    {
        if ($this->exitCode === null) {
            throw new InternalError('exit code is not set');
        }

        return new RuntimeResult(
            $this->exitCode,
            $this->result,
            $this->debugger,
            $this->error,
        );
    }
}
