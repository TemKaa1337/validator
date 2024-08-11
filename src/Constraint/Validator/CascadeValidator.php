<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint\Validator;

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

        if (!is_iterable($value) && !is_object($value)) {
            throw new UnexpectedTypeException(actualType: gettype($value), expectedType: 'object|iterable');
        }

        $value = is_iterable($value) ? $value : [$value];

        foreach ($value as $item) {
            if (!is_object($item)) {
                throw new UnexpectedTypeException(actualType: gettype($item), expectedType: 'object');
            }

            $errors = $this->validator->validate($item);

            $this->addErrors($errors);
        }
    }

    private function addErrors(ViolationListInterface $errors): void
    {
        foreach ($errors as $error) {
            $this->addViolation($error);
        }
    }
}
