<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\GoType\NamedType;

trait TypeableTrait
{
    use SealableTrait;
    use AddressableTrait;

    final public function becomeTyped(NamedType $type): AddressableValue&Sealable
    {
        $value = $this->doBecomeTyped($type);

        if ($this->isSealed()) {
            $value->seal();
        }

        if ($this->isAddressable()) {
            $value->makeAddressable();
        }

        return $value;
    }

    abstract protected function doBecomeTyped(NamedType $type): AddressableValue&Sealable;
}
