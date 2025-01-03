<?php

declare(strict_types=1);

namespace Temkaa\Validator\Constraint\Validator;

use Temkaa\Validator\AbstractConstraintValidator;
use Temkaa\Validator\Constraint\Assert\GreaterThan;
use Temkaa\Validator\Constraint\ConstraintInterface;
use Temkaa\Validator\Constraint\Violation;
use Temkaa\Validator\Exception\UnexpectedTypeException;
use Temkaa\Validator\Model\ValidatedValueInterface;
use function gettype;

/**
 * @internal
 *
 * @extends AbstractConstraintValidator<GreaterThan>
 */
final class GreaterThanValidator extends AbstractConstraintValidator
{
    /**
     * @param GreaterThan $constraint
     */
    public function validate(ValidatedValueInterface $validatedValue, ConstraintInterface $constraint): void
    {
        if (!$validatedValue->isInitialized()) {
            return;
        }

        $value = $validatedValue->getValue();
        if (!is_numeric($value)) {
            throw new UnexpectedTypeException(actualType: gettype($value), expectedType: 'float|int');
        }

        $value = (float) $value;
        $isInvalid = $constraint->allowEquality
            ? $value < $constraint->threshold
            : $value <= $constraint->threshold;

        if ($isInvalid) {
            $this->addViolation(
                new Violation(invalidValue: $value, message: $constraint->message, path: $validatedValue->getPath()),
            );
        }
    }
}
