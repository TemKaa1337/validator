<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint;

interface ConstraintValidatorInterface
{
    public function getViolations(): ViolationListInterface;

    public function validate(mixed $value, ConstraintInterface $constraint): void;
}
