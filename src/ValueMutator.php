<?php

declare(strict_types=1);

namespace GoPhp;

use GoPhp\Env\EnvValue\MutableValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Int\BaseIntValue;
use GoPhp\GoValue\Sequence;

final class ValueMutator
{
    private function __construct(
        private readonly \Closure $mutator,
        private readonly bool $compound,
    ) {}

    public static function fromEnvVar(MutableValue $var, bool $compound): self
    {
        return new self(
            $compound ?
                $var->unwrap()->mutate(...) :
                $var->set(...),
            $compound,
        );
    }

    public static function fromArrayValue(Sequence $array, BaseIntValue $index, bool $compound): self
    {
        return new self(
            $compound ?
                static function (Operator $op, GoValue $value) use ($array, $index): void {
                    $array->get($index->unwrap())->mutate($op, $value);
                } :
                static function (GoValue $value) use ($array, $index): void {
                    $array->set($value, $index->unwrap());
                },
            $compound,
        );
    }

    public function mutate(?Operator $op, GoValue $value): void
    {
        if ($this->compound) {
            ($this->mutator)($op, $value);
        } else {
            ($this->mutator)($value);
        }
    }
}
