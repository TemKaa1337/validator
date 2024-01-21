<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint\Validator;

use Countable;
use Stringable;
use Temkaa\SimpleValidator\AbstractConstraintValidator;
use Temkaa\SimpleValidator\Constraint\Assert\Length;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\Violation;
use Temkaa\SimpleValidator\Exception\InvalidConstraintConfigurationException;
use Temkaa\SimpleValidator\Exception\UnexpectedTypeException;

/**
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
final class LengthValidator extends AbstractConstraintValidator
{
    public function validate(mixed $value, ConstraintInterface $constraint): void
    {
        $this->validateConstraint($constraint);
        $this->validateValue($value);

        $length = is_string($value) || $value instanceof Stringable
            ? mb_strlen((string) $value)
            : count($value);

        /** @psalm-suppress NoInterfaceProperties */
        if ($constraint->minLength !== null && $constraint->minLength > $length) {
            $this->addViolation(new Violation(invalidValue: $value, message: $constraint->minMessage, path: null));
        } else if ($constraint->maxLength !== null && $constraint->maxLength < $length) {
            $this->addViolation(new Violation(invalidValue: $value, message: $constraint->maxMessage, path: null));
        }
    }

    private function validateConstraint(ConstraintInterface $constraint): void
    {
        if (!$constraint instanceof Length) {
            throw new UnexpectedTypeException(actualType: $constraint::class, expectedType: Length::class);
        }

        if ($constraint->minLength === null && $constraint->maxLength === null) {
            throw new InvalidConstraintConfigurationException(
                'Length constraint must have one of "minLength" or "maxLength" argument set.',
            );
        }

        if ($constraint->minMessage === null && $constraint->maxMessage === null) {
            throw new InvalidConstraintConfigurationException(
                'Length constraint must have one of "minLength" or "maxLength" argument set.',
            );
        }

        if (
            $constraint->minLength !== null && $constraint->minMessage === null
            || $constraint->minLength === null && $constraint->minMessage !== null
        ) {
            throw new InvalidConstraintConfigurationException(
                'Length constraint must have both "minLength" and "minMessage" arguments set.',
            );
        }

        if (
            $constraint->maxLength !== null && $constraint->maxMessage === null
            || $constraint->maxLength === null && $constraint->maxMessage !== null
        ) {
            throw new InvalidConstraintConfigurationException(
                'Length constraint must have both "maxLength" and "maxMessage" arguments set.',
            );
        }

        if ($constraint->minLength !== null && $constraint->maxLength !== null && $constraint->maxLength < $constraint->minLength) {
            throw new InvalidConstraintConfigurationException(
                'Argument "maxLength" of Length constraint must be equal or greater than "minLength" value.',
            );
        }

        if ($constraint->minLength !== null && $constraint->minLength < 0) {
            throw new InvalidConstraintConfigurationException(
                'Argument "minLength" of Length constraint must be equal or greater than 0.',
            );
        }

        if ($constraint->maxLength !== null && $constraint->maxLength < 0) {
            throw new InvalidConstraintConfigurationException(
                'Argument "maxLength" of Length constraint must be equal or greater than 0.',
            );
        }
    }

    private function validateValue(mixed $value): void
    {
        if (!is_string($value) && !is_array($value) && !$value instanceof Countable && !$value instanceof Stringable) {
            throw new UnexpectedTypeException(
                actualType: gettype($value),
                expectedType: 'array|\Countable|string|\Stringable',
            );
        }
    }
}
