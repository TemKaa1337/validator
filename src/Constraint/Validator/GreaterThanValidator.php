<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint\Validator;

use Temkaa\SimpleValidator\AbstractConstraintValidator;
use Temkaa\SimpleValidator\Constraint\Assert\GreaterThan;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\Violation;
use Temkaa\SimpleValidator\Exception\UnexpectedTypeException;

final class GreaterThanValidator extends AbstractConstraintValidator
{
    public function validate(mixed $value, ConstraintInterface $constraint): void
    {
        if (!$constraint instanceof GreaterThan) {
            throw new UnexpectedTypeException(actualType: $constraint::class, expectedType: GreaterThan::class);
        }

        if (!is_numeric($value)) {
            throw new UnexpectedTypeException(actualType: gettype($value), expectedType: 'float|int');
        }

        $value = (float) $value;
        $isInvalid = $constraint->allowEquality
            ? $value < $constraint->threshold
            : $value <= $constraint->threshold;

        if ($isInvalid) {
            $this->addViolation(new Violation(invalidValue: $value, message: $constraint->message, path: null));
        }
    }
}
