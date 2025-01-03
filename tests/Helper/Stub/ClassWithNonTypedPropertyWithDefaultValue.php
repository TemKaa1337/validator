<?php

declare(strict_types=1);

namespace Tests\Helper\Stub;

use Exception;
use Temkaa\Validator\Constraint\ConstraintInterface;
use Temkaa\Validator\Constraint\ConstraintValidatorInterface;
use Temkaa\Validator\Constraint\ViolationList;
use Temkaa\Validator\Constraint\ViolationListInterface;
use Temkaa\Validator\Model\ValidatedValueInterface;

final class ClassWithNonTypedPropertyWithDefaultValue implements ConstraintValidatorInterface
{
    public function __construct(
        public $class = null,
    ) {
    }

    /**
     * @throws Exception
     */
    public function getViolations(): ViolationListInterface
    {
        return new ViolationList();
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validate(ValidatedValueInterface $validatedValue, ConstraintInterface $constraint): void
    {
    }
}
