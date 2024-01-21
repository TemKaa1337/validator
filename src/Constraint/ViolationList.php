<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint;

use ArrayIterator;
use Traversable;

/**
 * @psalm-api
 * @implements ViolationListInterface<ViolationInterface>
 */
final class ViolationList implements ViolationListInterface
{
    /**
     * @param ViolationInterface[] $violations
     *
     * @psalm-api
     */
    public function __construct(
        private array $violations = [],
    ) {
    }

    public function add(ViolationInterface $violation): void
    {
        $this->violations[] = $violation;
    }

    public function count(): int
    {
        return count($this->violations);
    }

    /**
     * @return ViolationInterface[]
     */
    public function getAll(): array
    {
        return $this->violations;
    }

    /**
     * @return Traversable<ViolationInterface>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->violations);
    }

    /**
     * @param ViolationListInterface<ViolationInterface> $list
     */
    public function merge(ViolationListInterface $list): void
    {
        foreach ($list as $violation) {
            $this->violations[] = $violation;
        }
    }
}
