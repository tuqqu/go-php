<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Map;

use GoPhp\GoValue\GoValue;

final class RefKeyMap implements Map
{
    private \SplObjectStorage $values;
    private int $len;

    public function __construct()
    {
        $this->values = new \SplObjectStorage();
        $this->len = 0;
    }

    public function has(GoValue $at): bool
    {
        return isset($this->values[$at]);
    }

    public function get(GoValue $at): GoValue
    {
        return $this->values[$at];
    }

    public function set(GoValue $value, GoValue $at): void
    {
        if (!$this->has($at)) {
            ++$this->len;
        }

        $this->values->attach($at, $value);
    }

    public function len(): int
    {
        return $this->len;
    }

    /**
     * @return iterable<GoValue, GoValue>
     */
    public function iter(): iterable
    {
        for ($i = 0; $i < $this->len; ++$i) {
            /**
             * @var GoValue $key
             * @var GoValue $value
             */
            $key = $this->values->current();
            $value = $this->values->getInfo();

            $this->values->next();

            yield $key => $value;
        }

        $this->values->rewind();
    }
}
