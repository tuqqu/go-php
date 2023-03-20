<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Func;

use GoPhp\Error\RuntimeError;
use GoPhp\GoType\GoType;
use GoPhp\GoType\InterfaceType;
use GoPhp\GoType\PointerType;
use GoPhp\GoType\WrappedType;

/**
 * Receiver of a method.
 *
 * ```
 * func (r *ReceiverType) MethodName() {}
 *      ^^^^^^^^^^^^^^^^
 * ```
 *
 * @psalm-type ReceiverType = WrappedType|PointerType<WrappedType>
 */
final class Receiver
{
    /**
     * @param ReceiverType $type
     */
    private function __construct(
        public readonly ?string $name,
        public readonly WrappedType|PointerType $type,
    ) {}

    public static function fromParams(Params $params): self
    {
        if ($params->len !== 1) {
            throw RuntimeError::multipleReceivers();
        }

        $receiverParam = $params[0];
        self::validateReceiverType($receiverParam->type);

        return new self(
            $receiverParam->name,
            $receiverParam->type,
        );
    }

    public function getType(): WrappedType
    {
        return $this->type instanceof PointerType
            ? $this->type->pointsTo
            : $this->type;
    }

    /**
     * @psalm-assert ReceiverType $receiverType
     */
    private static function validateReceiverType(GoType $receiverType): void
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
    }
}
