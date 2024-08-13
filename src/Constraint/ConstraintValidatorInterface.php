<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint;

use Temkaa\SimpleValidator\Model\ValidatedValueInterface;

interface ConstraintValidatorInterface
{
    public function getViolations(): ViolationListInterface;

    public function validate(ValidatedValueInterface $value, ConstraintInterface $constraint): void;
}
