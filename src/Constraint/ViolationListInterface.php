<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint;

use Countable;
use IteratorAggregate;

/**
 * @psalm-api
 * @template-extends IteratorAggregate<ViolationInterface>
 */
interface ViolationListInterface extends Countable, IteratorAggregate
{
    public function add(ViolationInterface $violation): void;

    public function getAll(): array;

    public function merge(ViolationListInterface $list): void;
}
