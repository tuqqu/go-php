<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Func;

use Closure;
use GoPhp\Argv;
use GoPhp\Env\Environment;
use GoPhp\Error\InternalError;
use GoPhp\Error\PanicError;
use GoPhp\Error\RuntimeError;
use GoPhp\GoType\FuncType;
use GoPhp\GoValue\AddressableTrait;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\PointerValue;
use GoPhp\GoValue\RecoverableInvokable;
use GoPhp\GoValue\Ref;
use GoPhp\GoValue\SealableTrait;
use GoPhp\GoValue\UntypedNilValue;
use GoPhp\Operator;

use function GoPhp\assert_nil_comparison;
use function GoPhp\assert_values_compatible;
use function GoPhp\GoValue\get_address;

use const GoPhp\GoValue\NIL;

/**
 * @psalm-type FuncCallable = callable(Argv): GoValue
 * @psalm-import-type FuncBody from Func
 * @template-implements AddressableValue<FuncCallable>
 */
final class FuncValue implements RecoverableInvokable, Ref, AddressableValue
{
    use AddressableTrait;
    use SealableTrait;

    public const string NAME = 'func';

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
        Closure $body,
        FuncType $type,
        Environment $enclosure,
        ?Receiver $receiver,
        string $namespace,
    ): self {
        $innerFunc = new Func($body, $type, $enclosure, $receiver, $namespace);

        return new self($innerFunc, $type);
    }

    public static function nil(FuncType $type): self
    {
        return new self(NIL, $type);
    }

    /**
     * @psalm-assert !null $this->innerFunc
     */
    public function isNil(): bool
    {
        return $this->innerFunc === NIL;
    }

    public function zeroReturnValue(): GoValue
    {
        if ($this->isNil()) {
            throw PanicError::nilDereference();
        }

        return $this->innerFunc->zeroReturnValue();
    }

    public function bind(AddressableValue $instance): void
    {
        if ($this->isNil()) {
            throw InternalError::unexpectedValue(NIL);
        }

        $this->innerFunc->bind($instance);
    }

    public function copy(): self
    {
        return $this;
    }

    public function toString(): string
    {
        return get_address($this);
    }

    /**
     * @return FuncCallable
     */
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
                throw RuntimeError::cannotTakeAddressOfValue($this);
            }

            return PointerValue::fromValue($this);
        }

        throw RuntimeError::undefinedOperator($op, $this, true);
    }

    public function operateOn(Operator $op, GoValue $rhs): BoolValue
    {
        assert_nil_comparison($this, $rhs, self::NAME);

        return match ($op) {
            Operator::EqEq => new BoolValue($this->isNil()),
            Operator::NotEq => new BoolValue(!$this->isNil()),
            default => throw RuntimeError::undefinedOperator($op, $this),
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

        throw RuntimeError::undefinedOperator($op, $this);
    }
}
