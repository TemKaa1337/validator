<?php

declare(strict_types=1);

namespace Tests\Unit\Stub;

use Temkaa\SimpleValidator\AbstractConstraintValidator;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\Violation;
use Temkaa\SimpleValidator\Model\ValidatedValueInterface;

final class CustomValidator extends AbstractConstraintValidator
{
    public function __construct(
        private readonly CustomClass $class,
    ) {
        parent::__construct();
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validate(ValidatedValueInterface $value, ConstraintInterface $constraint): void
    {
        $this->addViolation(
            new Violation($value->getValue(), $this->class->getMessage(), path: null),
        );
    }
}
