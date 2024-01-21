<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint;

use ArrayIterator;
use Traversable;

/**
 * @psalm-api
 */
final class ViolationList implements ViolationListInterface
{
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

    public function getAll(): array
    {
        return $this->violations;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->violations);
    }

    public function merge(ViolationListInterface $list): void
    {
        foreach ($list as $violation) {
            $this->violations[] = $violation;
        }
    }
}
