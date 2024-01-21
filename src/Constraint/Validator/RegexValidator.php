<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint\Validator;

use Stringable;
use Temkaa\SimpleValidator\AbstractConstraintValidator;
use Temkaa\SimpleValidator\Constraint\Assert\Regex;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\Violation;
use Temkaa\SimpleValidator\Exception\UnexpectedTypeException;

final class RegexValidator extends AbstractConstraintValidator
{
    public function validate(mixed $value, ConstraintInterface $constraint): void
    {
        if (!$constraint instanceof Regex) {
            throw new UnexpectedTypeException(actualType: $constraint::class, expectedType: Regex::class);
        }

        if (!is_string($value) && !$value instanceof Stringable) {
            throw new UnexpectedTypeException(actualType: gettype($value), expectedType: 'string|\Stringable');
        }

        if (!preg_match($constraint->pattern, (string) $value)) {
            $this->addViolation(new Violation(invalidValue: $value, message: $constraint->message, path: null));
        }
    }
}
