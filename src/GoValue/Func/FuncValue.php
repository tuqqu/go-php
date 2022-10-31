<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Func;

use GoPhp\Argv;
use GoPhp\Env\Environment;
use GoPhp\Error\OperationError;
use GoPhp\Error\PanicError;
use GoPhp\GoType\FuncType;
use GoPhp\GoValue\AddressableTrait;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Invokable;
use GoPhp\GoValue\PointerValue;
use GoPhp\GoValue\SealableTrait;
use GoPhp\GoValue\UntypedNilValue;
use GoPhp\Operator;

use function GoPhp\assert_nil_comparison;
use function GoPhp\assert_values_compatible;

use const GoPhp\{NIL, ZERO_ADDRESS};

/**
 * @psalm-import-type FuncBody from Func
 */
final class FuncValue implements Invokable, AddressableValue
{
    use AddressableTrait;
    use SealableTrait;

    public const NAME = 'func'; //fixme move to types

    private function __construct(
        public ?Func $innerFunc,
        public readonly FuncType $type,
    ) {}

    public function __invoke(Argv $argv): GoValue
    {
        if ($this->innerFunc === NIL) {
            throw PanicError::nilDereference();
        }

        return ($this->innerFunc)($argv);
    }

    /**
     * @param FuncBody $body
     */
    public static function fromBody(
        \Closure $body,
        FuncType $type,
        Environment $enclosure,
        ?Param $receiver,
        string $namespace
    ): self {
        $innerFunc = new Func($body, $type, $enclosure, $receiver, $namespace);

        return new self($innerFunc, $type);
    }

    public function bind(AddressableValue $instance): void
    {
        $this->innerFunc->bind($instance);
    }

    public static function nil(FuncType $type): self
    {
        return new self(NIL, $type);
    }

    public function copy(): self
    {
        return $this;
    }

    public function toString(): string
    {
        return \sprintf('0x%x', $this->getAddress());
    }

    public function unwrap(): callable
    {
        return $this;
    }

    public function type(): FuncType
    {
        return $this->type;
    }

    public function operate(Operator $op): PointerValue
    {
        if ($op === Operator::BitAnd) {
            if ($this->isSealed()) {
                throw OperationError::cannotTakeAddressOfValue($this);
            }

            return PointerValue::fromValue($this);
        }

        throw OperationError::undefinedOperator($op, $this, true);
    }

    public function operateOn(Operator $op, GoValue $rhs): BoolValue
    {
        assert_nil_comparison($this, $rhs, self::NAME);

        return match ($op) {
            Operator::EqEq => new BoolValue($this->innerFunc === NIL),
            Operator::NotEq => new BoolValue($this->innerFunc !== NIL),
            default => throw OperationError::undefinedOperator($op, $this),
        };
    }

    public function mutate(Operator $op, GoValue $rhs): void
    {
        if ($op === Operator::Eq) {
            $this->onMutate();

            if ($rhs instanceof UntypedNilValue) {
                $this->innerFunc = NIL;

                return;
            }

            assert_values_compatible($this, $rhs);

            $this->innerFunc = $rhs->innerFunc;

            return;
        }

        throw OperationError::undefinedOperator($op, $this);
    }

    private function getAddress(): int
    {
        if ($this->innerFunc === NIL) {
            return ZERO_ADDRESS;
        }

        return \spl_object_id($this);
    }
}
