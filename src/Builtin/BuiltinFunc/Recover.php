<?php

declare(strict_types=1);

namespace GoPhp\Builtin\BuiltinFunc;

use GoPhp\Argv;
use GoPhp\GoType\InterfaceType;
use GoPhp\GoValue\Interface\InterfaceValue;
use GoPhp\PanicPointer;

use function GoPhp\assert_argc;

/**
 * @see https://pkg.go.dev/builtin#recover
 */
class Recover implements BuiltinFunc
{
    public function __construct(
        private readonly string $name,
        private readonly PanicPointer $panicPointer,
    ) {}

    public function __invoke(Argv $argv): InterfaceValue
    {
        assert_argc($this, $argv, 0);
        $lastPanic = $this->panicPointer->pointsTo();

        if ($lastPanic !== null) {
            $this->panicPointer->clear();

            return new InterfaceValue($lastPanic->panicValue);
        }

        // fixme type
        return InterfaceValue::nil(new InterfaceType());
    }

    public function name(): string
    {
        return $this->name;
    }
}
