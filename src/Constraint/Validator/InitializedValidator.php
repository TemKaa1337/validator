<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint\Validator;

use Temkaa\SimpleValidator\AbstractConstraintValidator;
use Temkaa\SimpleValidator\Constraint\Assert\Initialized;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\Violation;
use Temkaa\SimpleValidator\Exception\UnexpectedTypeException;
use Temkaa\SimpleValidator\Model\ValidatedValueInterface;

final class InitializedValidator extends AbstractConstraintValidator
{
    public function validate(ValidatedValueInterface $value, ConstraintInterface $constraint): void
    {
        if (!$constraint instanceof Initialized) {
            throw new UnexpectedTypeException(actualType: $constraint::class, expectedType: Initialized::class);
        }

        if (!$value->isInitialized()) {
            $this->addViolation(
                new Violation(invalidValue: $value->getValue(), message: $constraint->message, path: $value->getPath()),
            );
        }
    }
}
