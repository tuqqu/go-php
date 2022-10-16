<?php

declare(strict_types=1);

namespace GoPhp\Builtin\BuiltinFunc;

use GoPhp\Argv;
use GoPhp\Error\OperationError;
use GoPhp\GoType\MapType;
use GoPhp\GoType\SliceType;
use GoPhp\GoValue\Int\BaseIntValue;
use GoPhp\GoValue\Map\MapBuilder;
use GoPhp\GoValue\Map\MapValue;
use GoPhp\GoValue\Slice\SliceBuilder;
use GoPhp\GoValue\Slice\SliceValue;
use GoPhp\GoValue\TypeValue;

use function GoPhp\assert_arg_int;
use function GoPhp\assert_arg_value;
use function GoPhp\assert_argc;
use function GoPhp\assert_index_positive;

/**
 * @see https://pkg.go.dev/builtin#make
 */
class Make extends BaseBuiltinFunc
{
    public function __invoke(Argv $argv): SliceValue|MapValue
    {
        assert_argc($this, $argv, 2, true);
        assert_arg_value($argv[0], TypeValue::class, 'type');

        /** @var TypeValue $type */
        $type = $argv[0]->value;

        if ($type->type instanceof SliceType) {
            if ($argv->argc > 3) {
                throw OperationError::wrongArgumentNumber('2 or 3', $argv->argc);
            }

            $builder = SliceBuilder::fromType($type->type);

            if (isset($argv[1])) {
                assert_arg_int($argv[1]);

                $len = $argv[1]->value->unwrap();

                assert_index_positive($len);

                for ($i = 0; $i < $len; ++$i) {
                    $builder->pushBlindly($type->type->elemType->defaultValue());
                }
            }

            if (isset($argv[2])) {
                assert_arg_int($argv[2]);

                $cap = $argv[2]->value->unwrap();

                assert_index_positive($cap);

                if ($cap < ($len ?? 0)) {
                    throw OperationError::lenAndCapSwapped();
                }

                $builder->setCap($cap);
            }

            return $builder->build();
        }

        if ($type->type instanceof MapType) {
            if ($argv->argc > 2) {
                throw OperationError::wrongArgumentNumber(2, $argv->argc);
            }

            if (isset($argv[1])) {
                // we do not use this value, just validating it
                assert_arg_value($argv[1], BaseIntValue::class, BaseIntValue::NAME);
                assert_index_positive($argv[1]->value->unwrap());
            }

            return MapBuilder::fromType($type->type)->build();
        }

        throw OperationError::wrongArgumentType($argv[0], 'slice, map or channel');
    }

    public function expectsTypeAsFirstArg(): bool
    {
        return true;
    }
}
