<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator;

use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\ViolationListInterface;

/**
 * @psalm-api
 */
interface ValidatorInterface
{
    /**
     * @param iterable<object>|object                        $values
     * @param ConstraintInterface[]|ConstraintInterface|null $constraints
     *
     * @return ViolationListInterface
     */
    public function validate(
        iterable|object $values,
        array|ConstraintInterface|null $constraints = null,
    ): ViolationListInterface;
}
