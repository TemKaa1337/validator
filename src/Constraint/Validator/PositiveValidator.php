<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint\Validator;

use Temkaa\SimpleValidator\AbstractConstraintValidator;
use Temkaa\SimpleValidator\Constraint\Assert\Positive;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\Violation;
use Temkaa\SimpleValidator\Exception\UnexpectedTypeException;

final class PositiveValidator extends AbstractConstraintValidator
{
    public function validate(mixed $value, ConstraintInterface $constraint): void
    {
        if (!$constraint instanceof Positive) {
            throw new UnexpectedTypeException(actualType: $constraint::class, expectedType: Positive::class);
        }

        if (!is_numeric($value)) {
            throw new UnexpectedTypeException(actualType: gettype($value), expectedType: 'float|int');
        }

        $value = (float) $value;
        if ($value <= 0.0) {
            $this->addViolation(new Violation(invalidValue: $value, message: $constraint->message, path: null));
        }
    }
}
