<?php

declare(strict_types=1);

namespace Temkaa\Validator\Constraint\Validator;

use Temkaa\Validator\AbstractConstraintValidator;
use Temkaa\Validator\Constraint\Assert\Cascade;
use Temkaa\Validator\Constraint\ConstraintInterface;
use Temkaa\Validator\Model\ValidatedValueInterface;
use Temkaa\Validator\Utils\InputArgumentValidator;

/**
 * @internal
 *
 * @extends AbstractConstraintValidator<Cascade>
 */
final class CascadeValidator extends AbstractConstraintValidator
{
    private InputArgumentValidator $inputArgumentValidator;

    public function __construct()
    {
        parent::__construct();

        $this->inputArgumentValidator = new InputArgumentValidator();
    }

    /**
     * @param Cascade $constraint
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validate(ValidatedValueInterface $validatedValue, ConstraintInterface $constraint): void
    {
        if (!$validatedValue->isInitialized()) {
            return;
        }

        $this->inputArgumentValidator->validateValues($validatedValue->getValue());
    }
}
