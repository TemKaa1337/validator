<?php

declare(strict_types=1);

namespace Temkaa\Validator\Constraint\Validator;

use Countable;
use Temkaa\Validator\AbstractConstraintValidator;
use Temkaa\Validator\Constraint\Assert\Count;
use Temkaa\Validator\Constraint\ConstraintInterface;
use Temkaa\Validator\Constraint\Violation;
use Temkaa\Validator\Exception\UnexpectedTypeException;
use Temkaa\Validator\Model\ValidatedValueInterface;
use function count;
use function gettype;
use function is_array;

/**
 * @internal
 *
 * @extends AbstractConstraintValidator<Count>
 */
final class CountValidator extends AbstractConstraintValidator
{
    /**
     * @param Count $constraint
     */
    public function validate(ValidatedValueInterface $validatedValue, ConstraintInterface $constraint): void
    {
        if (!$validatedValue->isInitialized()) {
            return;
        }

        $value = $validatedValue->getValue();
        if (!is_array($value) && !$value instanceof Countable) {
            throw new UnexpectedTypeException(actualType: gettype($value), expectedType: 'array|\Countable');
        }

        if (count($value) !== $constraint->expected) {
            $this->addViolation(
                new Violation(invalidValue: $value, message: $constraint->message, path: $validatedValue->getPath()),
            );
        }
    }
}
