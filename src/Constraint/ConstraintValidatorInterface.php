<?php

declare(strict_types=1);

namespace Temkaa\Validator\Constraint;

use Temkaa\Validator\Model\ValidatedValueInterface;

/**
 * @api
 *
 * @template TConstraint of ConstraintInterface
 */
interface ConstraintValidatorInterface
{
    /**
     * @return ViolationListInterface<int, ViolationInterface>
     */
    public function getViolations(): ViolationListInterface;

    /**
     * @param TConstraint $constraint
     */
    public function validate(ValidatedValueInterface $validatedValue, ConstraintInterface $constraint): void;
}
