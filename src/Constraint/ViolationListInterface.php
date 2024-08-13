<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint;

use Countable;
use IteratorAggregate;

/**
 * @template T of ViolationInterface
 * @template-extends IteratorAggregate<ViolationInterface>
 *
 * @psalm-api
 */
interface ViolationListInterface extends Countable, IteratorAggregate
{
    public function add(ViolationInterface $violation): void;

    /**
     * @param ViolationListInterface<ViolationInterface> $list
     */
    public function merge(ViolationListInterface $list): void;
}
