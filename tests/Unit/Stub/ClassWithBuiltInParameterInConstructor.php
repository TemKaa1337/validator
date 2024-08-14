<?php

declare(strict_types=1);

namespace Tests\Unit\Stub;

use Exception;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\ConstraintValidatorInterface;
use Temkaa\SimpleValidator\Constraint\ViolationListInterface;
use Temkaa\SimpleValidator\Model\ValidatedValueInterface;

final readonly class ClassWithBuiltInParameterInConstructor implements ConstraintValidatorInterface
{
    public function __construct(
        public int $value,
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
    public function validate(ValidatedValueInterface $value, ConstraintInterface $constraint): void
    {
    }
}
