<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint\Validator;

use Temkaa\SimpleValidator\AbstractConstraintValidator;
use Temkaa\SimpleValidator\Constraint\Assert\Cascade;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Exception\UnexpectedTypeException;
use Temkaa\SimpleValidator\Model\ValidatedValueInterface;
use Temkaa\SimpleValidator\Utils\InputArgumentValidator;

final class CascadeValidator extends AbstractConstraintValidator
{
    public function validate(ValidatedValueInterface $value, ConstraintInterface $constraint): void
    {
        if (!$constraint instanceof Cascade) {
            throw new UnexpectedTypeException(actualType: $constraint::class, expectedType: Cascade::class);
        }

        if (!$value->isInitialized()) {
            return;
        }

        InputArgumentValidator::validateValues($value->getValue());
    }
}
