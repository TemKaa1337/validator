<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint\Validator;

use iterable;
use Temkaa\SimpleValidator\AbstractConstraintValidator;
use Temkaa\SimpleValidator\Constraint\Assert\Cascade;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\ViolationListInterface;
use Temkaa\SimpleValidator\Exception\UnexpectedTypeException;
use Temkaa\SimpleValidator\ValidatorInterface;

final class CascadeValidator extends AbstractConstraintValidator
{
    public function __construct(
        private readonly ValidatorInterface $validator,
    ) {
        parent::__construct();
    }

    public function validate(mixed $value, ConstraintInterface $constraint): void
    {
        if (!$constraint instanceof Cascade) {
            throw new UnexpectedTypeException(actualType: $constraint::class, expectedType: Cascade::class);
        }

        is_iterable();
        if ($value instanceof iterable) {

        }


        // option 1 - iterable of objects to validate
        // option 2 - object that needs to be validate
        $errors = $this->validator->validate($value, $constraint);
        foreach ($errors as $error) {
            $this->addViolation($error);
        }
    }

    private function addErrors(ViolationListInterface $errors): void
    {
        foreach ($errors as $error) {
            $this->addViolation($error);
        }
    }
}
