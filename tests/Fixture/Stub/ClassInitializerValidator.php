<?php

declare(strict_types=1);

namespace Tests\Fixture\Stub;

use ReflectionClass;
use Temkaa\SimpleValidator\AbstractConstraintValidator;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\Violation;
use Temkaa\SimpleValidator\Exception\UnexpectedTypeException;

final class ClassInitializerValidator extends AbstractConstraintValidator
{
    public function validate(mixed $value, ConstraintInterface $constraint): void
    {
        if (!$constraint instanceof ClassInitialized) {
            throw new UnexpectedTypeException(actualType: $constraint::class, expectedType: ClassInitialized::class);
        }

        if (!is_object($value)) {
            throw new UnexpectedTypeException(actualType: gettype($value), expectedType: 'object');
        }

        $r = new ReflectionClass($value);
        foreach ($r->getProperties() as $property) {
            if (!$property->isInitialized($value)) {
                $this->addViolation(
                    new Violation(invalidValue: $value, message: $constraint->message, path: $property->getName()),
                );

                return;
            }
        }
    }
}
