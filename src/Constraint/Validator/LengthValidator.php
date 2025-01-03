<?php

declare(strict_types=1);

namespace Temkaa\Validator\Constraint\Validator;

use Countable;
use Stringable;
use Temkaa\Validator\AbstractConstraintValidator;
use Temkaa\Validator\Constraint\Assert\Length;
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
 * @extends AbstractConstraintValidator<Length>
 */
final class LengthValidator extends AbstractConstraintValidator
{
    /**
     * @param Length $constraint
     */
    public function validate(ValidatedValueInterface $validatedValue, ConstraintInterface $constraint): void
    {
        if (!$validatedValue->isInitialized()) {
            return;
        }

        $errorPath = $validatedValue->getPath();
        $value = $validatedValue->getValue();

        $this->validateValue($value);

        /** @var string|array<int|string, mixed>|Stringable|Countable $value */
        $length = is_string($value) || $value instanceof Stringable
            ? mb_strlen((string) $value)
            : count($value);

        if ($constraint->minLength !== null && $constraint->minLength > $length) {
            /** @phpstan-ignore argument.type */
            $violation = new Violation(invalidValue: $value, message: $constraint->minMessage, path: $errorPath);

            $this->addViolation($violation);
        } else if ($constraint->maxLength !== null && $constraint->maxLength < $length) {
            /** @phpstan-ignore argument.type */
            $violation = new Violation(invalidValue: $value, message: $constraint->maxMessage, path: $errorPath);

            $this->addViolation($violation);
        }
    }

    private function validateValue(mixed $value): void
    {
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        if (!is_string($value) && !$value instanceof Stringable && !is_array($value) && !$value instanceof Countable) {
            throw new UnexpectedTypeException(
                actualType: gettype($value),
                expectedType: 'array|\Countable|string|\Stringable',
            );
        }
    }
}
