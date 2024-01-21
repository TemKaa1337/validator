<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint\Validator;

use Temkaa\SimpleValidator\AbstractConstraintValidator;
use Temkaa\SimpleValidator\Constraint\Assert\Initialized;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\Violation;
use Temkaa\SimpleValidator\Exception\UnexpectedTypeException;

final class InitializedValidator extends AbstractConstraintValidator
{
    public function validate(mixed $value, ConstraintInterface $constraint): void
    {
        if (!$constraint instanceof Initialized) {
            throw new UnexpectedTypeException(actualType: $constraint::class, expectedType: Initialized::class);
        }

        if (!is_bool($value)) {
            throw new UnexpectedTypeException(actualType: gettype($value), expectedType: 'boolean');
        }

        if (!$value) {
            $this->addViolation(new Violation(invalidValue: '', message: $constraint->message, path: null));
        }
    }
}
