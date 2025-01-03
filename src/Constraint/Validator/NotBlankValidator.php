<?php

declare(strict_types=1);

namespace Temkaa\Validator\Constraint\Validator;

use Countable;
use Stringable;
use Temkaa\Validator\AbstractConstraintValidator;
use Temkaa\Validator\Constraint\Assert\NotBlank;
use Temkaa\Validator\Constraint\ConstraintInterface;
use Temkaa\Validator\Constraint\Violation;
use Temkaa\Validator\Exception\UnexpectedTypeException;
use Temkaa\Validator\Model\ValidatedValueInterface;
use function count;
use function gettype;
use function is_array;
use function is_string;
use function mb_strlen;

/**
 * @internal
 *
 * @extends AbstractConstraintValidator<NotBlank>
 */
final class NotBlankValidator extends AbstractConstraintValidator
{
    /**
     * @param NotBlank $constraint
     */
    public function validate(ValidatedValueInterface $validatedValue, ConstraintInterface $constraint): void
    {
        $errorPath = $validatedValue->getPath();
        if (!$validatedValue->isInitialized()) {
            $this->addViolation(
                new Violation(
                    invalidValue: $validatedValue->getValue(), message: $constraint->message, path: $errorPath,
                ),
            );

            return;
        }

        $value = $validatedValue->getValue();

        /** @var null|string|Stringable|array<int|string, mixed>|Countable $value */
        $this->validateType($value);

        if ($value === null) {
            if (!$constraint->allowNull) {
                $this->addViolation(
                    new Violation(invalidValue: $value, message: $constraint->message, path: $errorPath),
                );
            }

            return;
        }

        $length = is_string($value) || $value instanceof Stringable ? mb_strlen((string) $value) : count($value);

        if ($length === 0) {
            $this->addViolation(new Violation(invalidValue: $value, message: $constraint->message, path: $errorPath));
        }
    }

    private function validateType(mixed $value): void
    {
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        if (
            $value !== null
            && !is_string($value)
            && !$value instanceof Stringable
            && !is_array($value)
            && !$value instanceof Countable
        ) {
            throw new UnexpectedTypeException(
                actualType: gettype($value),
                expectedType: 'array|\Countable|string|\Stringable|null',
            );
        }
    }
}
