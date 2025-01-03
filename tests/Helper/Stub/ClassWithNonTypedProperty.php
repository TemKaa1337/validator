<?php

declare(strict_types=1);

namespace Tests\Helper\Stub;

use Exception;
use Temkaa\Validator\Constraint\ConstraintInterface;
use Temkaa\Validator\Constraint\ConstraintValidatorInterface;
use Temkaa\Validator\Constraint\ViolationListInterface;
use Temkaa\Validator\Model\ValidatedValueInterface;

final class ClassWithNonTypedProperty implements ConstraintValidatorInterface
{
    public function __construct(
        public $class,
    ) {
    }

    /**
     * @throws Exception
     */
    public function getViolations(): ViolationListInterface
    {
        throw new Exception();
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validate(ValidatedValueInterface $validatedValue, ConstraintInterface $constraint): void
    {
    }
}
