<?php

declare(strict_types=1);

namespace Temkaa\Validator\Constraint\Validator;

use Temkaa\Validator\AbstractConstraintValidator;
use Temkaa\Validator\Constraint\Assert\Range;
use Temkaa\Validator\Constraint\ConstraintInterface;
use Temkaa\Validator\Constraint\Violation;
use Temkaa\Validator\Exception\UnexpectedTypeException;
use Temkaa\Validator\Model\ValidatedValueInterface;
use function gettype;

/**
 * @internal
 *
 * @extends AbstractConstraintValidator<Range>
 */
final class RangeValidator extends AbstractConstraintValidator
{
    /**
     * @param Range $constraint
     */
    public function validate(ValidatedValueInterface $validatedValue, ConstraintInterface $constraint): void
    {
        if (!$validatedValue->isInitialized()) {
            return;
        }

        $errorPath = $validatedValue->getPath();
        $value = $validatedValue->getValue();

        /** @var string|int|float $value */
        $this->validateValue($value);

        $value = (float) $value;
        if ($constraint->min !== null && $constraint->min > $value) {
            /** @phpstan-ignore argument.type */
            $violation = new Violation(invalidValue: $value, message: $constraint->minMessage, path: $errorPath);

            $this->addViolation($violation);
        } else if ($constraint->max !== null && $constraint->max < $value) {
            /** @phpstan-ignore argument.type */
            $violation = new Violation(invalidValue: $value, message: $constraint->maxMessage, path: $errorPath);

            $this->addViolation($violation);
        }
    }

    private function validateValue(mixed $value): void
    {
        if (!is_numeric($value)) {
            throw new UnexpectedTypeException(actualType: gettype($value), expectedType: 'float|int');
        }
    }
}
