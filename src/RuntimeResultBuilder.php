<?php

declare(strict_types=1);

namespace GoPhp;

use GoPhp\Debug\Debugger;
use GoPhp\Error\GoError;
use GoPhp\Error\InternalError;
use GoPhp\GoValue\GoValue;

final class RuntimeResultBuilder
{
    private ?GoValue $result = null;
    private ?Debugger $debugger = null;
    private ?GoError $error = null;
    private ?ExitCode $exitCode = null;

    public function setExitCode(ExitCode $exitCode): self
    {
        $this->exitCode = $exitCode;

        return $this;
    }

    public function setResult(?GoValue $result): self
    {
        $this->result = $result;

        return $this;
    }

    public function setDebugger(?Debugger $debugger): self
    {
        $this->debugger = $debugger;

        return $this;
    }

    public function setError(?GoError $error): self
    {
        $this->error = $error;

        return $this;
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
