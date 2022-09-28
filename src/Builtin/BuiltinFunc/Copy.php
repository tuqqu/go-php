<?php

declare(strict_types=1);

namespace GoPhp\Builtin\BuiltinFunc;

use GoPhp\GoType\BasicType;
use GoPhp\GoType\NamedType;
use GoPhp\GoType\SliceType;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Int\IntValue;
use GoPhp\GoValue\Slice\SliceValue;
use GoPhp\GoValue\StringValue;

use function GoPhp\assert_arg_type;
use function GoPhp\assert_arg_value;
use function GoPhp\assert_argc;

/**
 * @see https://pkg.go.dev/builtin#copy
 */
class Copy extends BaseBuiltinFunc
{
    public function __invoke(GoValue ...$argv): IntValue
    {
        assert_argc($this, $argv, 2);

        /** @var array{SliceValue, SliceValue|StringValue} $argv */
        $dst = $argv[0];
        $src = $argv[1];

        assert_arg_value($dst, SliceValue::class, 'slice', 1);

        // As a special case, if the destination's type is []byte,
        // copy also accepts a source argument with type string.
        $srcType = $src->type();

        if ($srcType instanceof BasicType && $srcType->isString()) {
            assert_arg_type($dst, new SliceType(NamedType::Byte), 1);
        } else {
            assert_arg_type($src, $dst->type(), 2);
        }

        $i = 0;
        $until = $dst->len();

        foreach ($src->iter() as $value) {
            if ($i >= $until) {
                break;
            }

            $dst->setBlindly($value, $i);
            ++$i;
        }

        return new IntValue($i);
    }
}
