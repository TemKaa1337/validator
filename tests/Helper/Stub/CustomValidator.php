<?php

declare(strict_types=1);

namespace Tests\Helper\Stub;

use Temkaa\Validator\AbstractConstraintValidator;
use Temkaa\Validator\Constraint\ConstraintInterface;
use Temkaa\Validator\Constraint\Violation;
use Temkaa\Validator\Model\ValidatedValueInterface;

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
            new Violation($value->getValue(), $this->class->getMessage(), path: 'path'),
        );
    }
}
