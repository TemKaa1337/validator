<?php

declare(strict_types=1);

namespace Temkaa\Validator\Constraint\Validator;

use Temkaa\Validator\AbstractConstraintValidator;
use Temkaa\Validator\Constraint\Assert\Initialized;
use Temkaa\Validator\Constraint\ConstraintInterface;
use Temkaa\Validator\Constraint\Violation;
use Temkaa\Validator\Model\ValidatedValueInterface;

/**
 * @internal
 *
 * @extends AbstractConstraintValidator<Initialized>
 */
final class InitializedValidator extends AbstractConstraintValidator
{
    /**
     * @param Initialized $constraint
     */
    public function validate(ValidatedValueInterface $validatedValue, ConstraintInterface $constraint): void
    {
        if (!$validatedValue->isInitialized()) {
            $this->addViolation(
                new Violation(
                    invalidValue: $validatedValue->getValue(),
                    message: $constraint->message,
                    path: $validatedValue->getPath(),
                ),
            );
        }
    }
}
