<?php

declare(strict_types=1);

namespace GoPhp\Builtin\BuiltinFunc;

use GoPhp\Error\OperationError;
use GoPhp\GoType\MapType;
use GoPhp\GoType\SliceType;
use GoPhp\GoValue\GoValue;
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
class Make implements BuiltinFunc
{
    public function __construct(
        private readonly string $name,
    ) {}

    public function __invoke(GoValue ...$argv): SliceValue|MapValue
    {
        assert_argc($argv, 1, variadic: true);
        assert_arg_value($argv[0], TypeValue::class, 'type', 1);

        /** @var TypeValue $type */
        $type = $argv[0];
        $argc = \count($argv);

        if ($type->type instanceof SliceType) {
            if ($argc > 3) {
                throw OperationError::wrongArgumentNumber('2 or 3', $argc);
            }

            $builder = SliceBuilder::fromType($type->type);

            if (isset($argv[1])) {
                assert_arg_int($argv[1], 2);

                $len = $argv[1]->unwrap();

                assert_index_positive($len);

                for ($i = 0; $i < $len; ++$i) {
                    $builder->pushBlindly($type->type->elemType->defaultValue());
                }
            }

            if (isset($argv[2])) {
                assert_arg_int($argv[2], 3);

                $cap = $argv[2]->unwrap();

                assert_index_positive($cap);

                if ($cap < ($len ?? 0)) {
                    throw OperationError::lenAndCapSwapped();
                }

                $builder->setCap($cap);
            }

            return $builder->build();
        }

        if ($type->type instanceof MapType) {
            if ($argc > 2) {
                throw OperationError::wrongArgumentNumber(2, $argc);
            }

            if (isset($argv[1])) {
                // we do not use this value, just validating it
                assert_arg_value($argv[1], BaseIntValue::class, 'int', 3);
                assert_index_positive($argv[1]->unwrap());
            }

            return MapBuilder::fromType($type->type)->build();
        }

        throw OperationError::wrongArgumentType($type->type, 'slice, map or channel', 1);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function expectsTypeAsFirstArg(): bool
    {
        return true;
    }
}