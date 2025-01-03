<?php

declare(strict_types=1);

namespace Temkaa\Validator\Constraint\Validator;

use Stringable;
use Temkaa\Validator\AbstractConstraintValidator;
use Temkaa\Validator\Constraint\Assert\Regex;
use Temkaa\Validator\Constraint\ConstraintInterface;
use Temkaa\Validator\Constraint\Violation;
use Temkaa\Validator\Exception\UnexpectedTypeException;
use Temkaa\Validator\Model\ValidatedValueInterface;
use function gettype;
use function is_string;

/**
 * @internal
 *
 * @extends AbstractConstraintValidator<Regex>
 */
final class RegexValidator extends AbstractConstraintValidator
{
    /**
     * @param Regex $constraint
     */
    public function validate(ValidatedValueInterface $validatedValue, ConstraintInterface $constraint): void
    {
        if (!$validatedValue->isInitialized()) {
            return;
        }

        $errorPath = $validatedValue->getPath();
        $value = $validatedValue->getValue();
        if (!is_string($value) && !$value instanceof Stringable) {
            throw new UnexpectedTypeException(actualType: gettype($value), expectedType: 'string|\Stringable');
        }

        if (!preg_match($constraint->pattern, (string) $value)) {
            $this->addViolation(new Violation(invalidValue: $value, message: $constraint->message, path: $errorPath));
        }
    }
}
