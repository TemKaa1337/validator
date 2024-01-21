<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint\Validator;

use Countable;
use Stringable;
use Temkaa\SimpleValidator\AbstractConstraintValidator;
use Temkaa\SimpleValidator\Constraint\Assert\NotBlank;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\Violation;
use Temkaa\SimpleValidator\Exception\UnexpectedTypeException;

final class NotBlankValidator extends AbstractConstraintValidator
{
    public function validate(mixed $value, ConstraintInterface $constraint): void
    {
        $this->performBasicValidation($value, $constraint);

        if ($value === null) {
            /** @psalm-suppress NoInterfaceProperties */
            if (!$constraint->allowNull) {
                $this->addViolation(new Violation(invalidValue: $value, message: $constraint->message, path: null));
            }

            return;
        }

        $length = match (true) {
            is_string($value) || $value instanceof Stringable => mb_strlen((string) $value),
            default                                           => count($value),
        };

        if ($length === 0) {
            /** @psalm-suppress NoInterfaceProperties */
            $this->addViolation(new Violation(invalidValue: $value, message: $constraint->message, path: null));
        }
    }

    private function performBasicValidation(mixed $value, ConstraintInterface $constraint): void
    {
        if (!$constraint instanceof NotBlank) {
            throw new UnexpectedTypeException(actualType: $constraint::class, expectedType: NotBlank::class);
        }

        if (
            $value !== null
            && !is_string($value)
            && !is_array($value)
            && !$value instanceof Stringable
            && !$value instanceof Countable
        ) {
            throw new UnexpectedTypeException(
                actualType: gettype($value),
                expectedType: 'array|\Countable|string|\Stringable|null',
            );
        }
    }
}
