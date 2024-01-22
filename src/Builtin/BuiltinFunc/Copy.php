<?php

declare(strict_types=1);

namespace GoPhp\Builtin\BuiltinFunc;

use GoPhp\Argv;
use GoPhp\GoType\BasicType;
use GoPhp\GoType\NamedType;
use GoPhp\GoType\SliceType;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\Int\IntNumber;
use GoPhp\GoValue\Int\IntValue;
use GoPhp\GoValue\Sequence;
use GoPhp\GoValue\Slice\SliceValue;

use function GoPhp\assert_arg_type;
use function GoPhp\assert_arg_value;
use function GoPhp\assert_argc;

/**
 * @see https://pkg.go.dev/builtin#copy
 */
class Copy implements BuiltinFunc
{
    public function __construct(
        private readonly string $name,
    ) {}

    public function __invoke(Argv $argv): IntValue
    {
        assert_argc($this, $argv, 2);

        $dst = $argv[0];
        $src = $argv[1];

        assert_arg_value($dst, SliceValue::class);

        // As a special case, if the destination's type is []byte,
        // copy also accepts a source argument with type string.
        $srcType = $src->value->type();

        if ($srcType instanceof BasicType && $srcType->isString()) {
            assert_arg_type($dst, new SliceType(NamedType::Byte));
        } else {
            assert_arg_type($src, $dst->value->type());
        }

        $dst = $dst->value;
        $src = $src->value;

        /**
         * @var SliceValue<AddressableValue> $dst
         * @var Sequence<IntNumber, AddressableValue> $src
         */
        $copied = $dst->copyFromSequence($src);

        return new IntValue($copied);
    }

    public function name(): string
    {
        return $this->name;
    }
}
