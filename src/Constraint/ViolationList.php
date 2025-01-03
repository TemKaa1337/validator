<?php

declare(strict_types=1);

namespace Temkaa\Validator\Constraint;

use ArrayIterator;
use Traversable;
use function count;

/**
 * @api
 *
 * @template TKey of int
 * @template TValue of ViolationInterface
 * @template-implements ViolationListInterface<TKey, TValue>
 */
final class ViolationList implements ViolationListInterface
{
    /**
     * @param array<TKey, TValue> $violations
     */
    public function __construct(
        private array $violations = [],
    ) {
    }

    /**
     * @param TValue $violation
     */
    public function add(ViolationInterface $violation): void
    {
        $this->violations[] = $violation;
    }

    public function count(): int
    {
        return count($this->violations);
    }

    /**
     * @return ArrayIterator<TKey, TValue>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->violations);
    }

    /**
     * @param ViolationListInterface<TKey, TValue> $list
     */
    public function merge(ViolationListInterface $list): void
    {
        foreach ($list as $violation) {
            $this->violations[] = $violation;
        }
    }
}
