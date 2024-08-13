<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint\Validator;

use Countable;
use Temkaa\SimpleValidator\AbstractConstraintValidator;
use Temkaa\SimpleValidator\Constraint\Assert\Count;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\Violation;
use Temkaa\SimpleValidator\Exception\UnexpectedTypeException;
use Temkaa\SimpleValidator\Model\ValidatedValueInterface;

final class CountValidator extends AbstractConstraintValidator
{
    public function validate(ValidatedValueInterface $value, ConstraintInterface $constraint): void
    {
        if (!$constraint instanceof Count) {
            throw new UnexpectedTypeException(actualType: $constraint::class, expectedType: Count::class);
        }

        if (!$value->isInitialized()) {
            return;
        }

        $errorPath = $value->getPath();
        $value = $value->getValue();
        if (!is_array($value) && !$value instanceof Countable) {
            throw new UnexpectedTypeException(actualType: gettype($value), expectedType: 'array|\Countable');
        }

        if (count($value) !== $constraint->expected) {
            $this->addViolation(new Violation(invalidValue: $value, message: $constraint->message, path: $errorPath));
        }
    }
}
