<?php

declare(strict_types=1);

namespace Temkaa\Validator\Constraint;

use Countable;
use IteratorAggregate;

/**
 * @api
 *
 * @template TKey of int
 * @template TValue of ViolationInterface
 * @template-extends IteratorAggregate<TKey, TValue>
 */
interface ViolationListInterface extends Countable, IteratorAggregate
{
    public function add(ViolationInterface $violation): void;

    /**
     * @param ViolationListInterface<TKey, TValue> $list
     */
    public function merge(ViolationListInterface $list): void;
}
