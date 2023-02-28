<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Func;

use GoPhp\Error\RuntimeError;
use GoPhp\GoType\GoType;
use GoPhp\GoType\InterfaceType;
use GoPhp\GoType\PointerType;
use GoPhp\GoType\WrappedType;

final class Receiver
{
    private function __construct(
        public readonly ?string $name,
        public readonly WrappedType $type,
    ) {}

    public static function fromParams(Params $params): self
    {
        if ($params->len !== 1) {
            throw RuntimeError::multipleReceivers();
        }

        $receiverParam = $params[0];

        return new self(
            $receiverParam->name,
            self::validateType($receiverParam->type),
        );
    }

    private static function validateType(GoType $receiverType): WrappedType
    {
        if ($receiverType instanceof PointerType) {
            $receiverType = $receiverType->pointsTo;
        }

        // technically WrappedType is the only type that can be a receiver
        if (!$receiverType instanceof WrappedType) {
            throw RuntimeError::invalidReceiverType($receiverType);
        }

        $underlyingType = $receiverType->unwind();

        if ($underlyingType instanceof PointerType || $underlyingType instanceof InterfaceType) {
            throw RuntimeError::invalidReceiverNamedType($receiverType);
        }

        return $receiverType;
    }
}
