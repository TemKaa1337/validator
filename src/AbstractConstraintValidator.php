<?php

declare(strict_types=1);

namespace Temkaa\Validator;

use Temkaa\Validator\Constraint\ConstraintInterface;
use Temkaa\Validator\Constraint\ConstraintValidatorInterface;
use Temkaa\Validator\Constraint\ViolationInterface;
use Temkaa\Validator\Constraint\ViolationList;
use Temkaa\Validator\Constraint\ViolationListInterface;

/**
 * @api
 *
 * @template TConstraint of ConstraintInterface
 * @implements ConstraintValidatorInterface<TConstraint>
 */
abstract class AbstractConstraintValidator implements ConstraintValidatorInterface
{
    /**
     * @var ViolationListInterface<int, ViolationInterface>
     */
    private readonly ViolationListInterface $violations;

    public function __construct()
    {
        $this->violations = new ViolationList();
    }

    /**
     * @return ViolationListInterface<int, ViolationInterface>
     */
    public function getViolations(): ViolationListInterface
    {
        return $this->violations;
    }

    protected function addViolation(ViolationInterface $violation): void
    {
        $this->violations->add($violation);
    }
}
