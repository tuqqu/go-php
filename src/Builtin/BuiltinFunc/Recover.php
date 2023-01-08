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
class Recover extends BaseBuiltinFunc
{
    public function __construct(
        string $name,
        private readonly PanicPointer $panicPointer,
    ) {
        parent::__construct($name);
    }

    public function __invoke(Argv $argv): InterfaceValue
    {
        assert_argc($this, $argv, 0);

        if ($this->panicPointer->panic !== null) {
            $lastPanic = $this->panicPointer->panic;
            $this->panicPointer->panic = null;

            return new InterfaceValue($lastPanic->panicValue);
        }

        // fixme type
        return InterfaceValue::nil(new InterfaceType());
    }
}
