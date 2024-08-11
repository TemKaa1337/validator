<?php

declare(strict_types=1);

namespace Tests\Unit\Stub;

use Temkaa\SimpleValidator\AbstractConstraintValidator;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\Violation;

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
    public function validate(mixed $value, ConstraintInterface $constraint): void
    {
        $this->addViolation(
            new Violation($value, $this->class->getMessage(), path: null),
        );
    }
}
