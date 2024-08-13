<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint\Validator;

use Temkaa\SimpleValidator\AbstractConstraintValidator;
use Temkaa\SimpleValidator\Constraint\Assert\Cascade;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\Violation;
use Temkaa\SimpleValidator\Exception\UnexpectedTypeException;
use Temkaa\SimpleValidator\Model\ValidatedValueInterface;
use Temkaa\SimpleValidator\ValidatorInterface;

final class CascadeValidator extends AbstractConstraintValidator
{
    public function __construct(
        private readonly ValidatorInterface $validator,
    ) {
        parent::__construct();
    }

    public function validate(ValidatedValueInterface $value, ConstraintInterface $constraint): void
    {
        if (!$constraint instanceof Cascade) {
            throw new UnexpectedTypeException(actualType: $constraint::class, expectedType: Cascade::class);
        }

        if (!$value->isInitialized()) {
            return;
        }

        // TODO: validation errors as in validator? eg InputArgumentValidator or this one
        // InputArgumentValidator::validateValues($value);
        // $errors = $this->validator->validate($value);
        //
        // $this->addErrors($errors);

        $errorPath = $value->getPath();
        $value = $value->getValue();

        if (!is_iterable($value) && !is_object($value)) {
            throw new UnexpectedTypeException(actualType: gettype($value), expectedType: 'object|iterable');
        }

        $isIterable = is_iterable($value);
        $value = $isIterable ? $value : [$value];

        foreach ($value as $index => $item) {
            if (!is_object($item)) {
                throw new UnexpectedTypeException(actualType: gettype($item), expectedType: 'object');
            }

            $errors = $this->validator->validate($item);

            foreach ($errors as $error) {
                $violationErrorPath = $isIterable
                    ? sprintf('%s[%s].%s', $errorPath, $index, $error->getPath())
                    : sprintf('%s.%s', $errorPath, $error->getPath());

                $this->addViolation(
                    new Violation($error->getInvalidValue(), $error->getMessage(), $violationErrorPath),
                );
            }
        }
    }
}
