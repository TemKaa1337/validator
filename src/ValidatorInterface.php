<?php

declare(strict_types=1);

namespace Temkaa\Validator;

use Temkaa\Validator\Constraint\ConstraintInterface;
use Temkaa\Validator\Constraint\ViolationInterface;
use Temkaa\Validator\Constraint\ViolationListInterface;

/**
 * @api
 */
interface ValidatorInterface
{
    /**
     * @template TConstraint of ConstraintInterface
     *
     * @param iterable<object>|object            $values
     * @param list<TConstraint>|TConstraint|null $constraints
     *
     * @return ViolationListInterface<int, ViolationInterface>
     */
    public function validate(
        iterable|object $values,
        array|ConstraintInterface|null $constraints = null,
    ): ViolationListInterface;
}
