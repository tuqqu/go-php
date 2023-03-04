<?php

declare(strict_types=1);

namespace GoPhp;

use GoPhp\Error\RuntimeError;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\BuiltinFuncValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Invokable;
use GoPhp\GoValue\Slice\SliceValue;
use GoPhp\GoValue\String\BaseString;
use GoPhp\GoValue\Unpackable;

final class ArgvBuilder
{
    /** @var array<int, GoValue> */
    private array $values;
    private int $argc;
    private bool $unpacked = false;

    /**
     * @param array<int, GoValue> $values
     */
    public function __construct(array $values = [])
    {
        $this->values = $values;
        $this->argc = \count($values);
    }

    public function add(GoValue $value): void
    {
        $this->values[] = $value;
        ++$this->argc;
    }

    public function markUnpacked(Invokable $func): void
    {
        $unpackable = $this->values[$this->argc - 1];

        $this->unpacked = match (true) {
            $unpackable instanceof SliceValue,
            $unpackable instanceof BaseString
            && $func instanceof BuiltinFuncValue
            && $func->func->permitsStringUnpacking() => true,
            default => throw RuntimeError::expectedSliceInArgumentUnpacking($unpackable, $func),
        };
    }

    public function build(): Argv
    {
        /** @var list<Arg> $argv */
        $argv = [];
        foreach ($this->values as $i => $value) {
            $argv[] = new Arg($i + 1, $value);
        }

        if ($this->unpacked) {
            /** @var Arg<Unpackable&AddressableValue> $unpackable */
            $unpackable = \array_pop($argv);

            foreach ($unpackable->value->unpack() as $value) {
                $argv[] = new Arg($unpackable->pos, $value);;
            }
        }

        return new Argv($argv, $this->argc);
    }
}
