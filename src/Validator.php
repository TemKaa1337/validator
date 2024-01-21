<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator;

use ReflectionClass;
use Temkaa\SimpleValidator\Constraint\Assert\Initialized;
use Temkaa\SimpleValidator\Constraint\Assert\NotBlank;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\ViolationList;
use Temkaa\SimpleValidator\Constraint\ViolationListInterface;
use Temkaa\SimpleValidator\Exception\UnsupportedActionException;

final class Validator implements ValidatorInterface
{
    public function validate(object $value, array|ConstraintInterface|null $constraints = null): ViolationListInterface
    {
        $this->validateConstraints($constraints);

        $constraints = match (true) {
            $constraints === null  => [],
            is_array($constraints) => $constraints,
            default                => [$constraints]
        };

        return $this->validateValue($value, $constraints);
    }

    private function validateConstraints(array|ConstraintInterface|null $constraints): void
    {
        if (!is_array($constraints)) {
            return;
        }

        foreach ($constraints as $constraint) {
            if (!$constraint instanceof ConstraintInterface) {
                throw new UnsupportedActionException(
                    sprintf(
                        'Cannot validate value with constraint of type "%s" as it does not implement "%s".',
                        gettype($constraint),
                        ConstraintInterface::class,
                    ),
                );
            }
        }
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @param object                $value
     * @param ConstraintInterface[] $constraints
     *
     * @return ViolationListInterface
     */
    private function validateValue(object $value, array $constraints): ViolationListInterface
    {
        $violations = new ViolationList();

        $r = new ReflectionClass($value);
        if ($r->isInternal()) {
            return $violations;
        }

        if ($constraints) {
            foreach ($constraints as $constraint) {
                $handler = $constraint->getHandler();
                $handler->validate($value, $constraint);

                $violations->merge($handler->getViolations());
            }

            return $violations;
        }

        $attributes = $r->getAttributes();
        foreach ($attributes as $attribute) {
            $constraint = $attribute->newInstance();
            if ($constraint instanceof ConstraintInterface) {
                $handler = $constraint->getHandler();
                $handler->validate($value, $constraint);

                $violations->merge($handler->getViolations());
            }
        }

        $properties = $r->getProperties();
        foreach ($properties as $property) {
            $attributes = $property->getAttributes();

            foreach ($attributes as $attribute) {
                $constraint = $attribute->newInstance();

                if ($constraint instanceof ConstraintInterface) {
                    $handler = $constraint->getHandler();
                    /** @psalm-suppress PossiblyInvalidArgument */
                    $isPropertyInitialized = $property->isInitialized($value);

                    if ($constraint instanceof Initialized || $constraint instanceof NotBlank) {
                        /** @psalm-suppress PossiblyInvalidArgument */
                        $value = match (true) {
                            $constraint instanceof NotBlank => $isPropertyInitialized
                                ? $property->getValue($value)
                                : '',
                            default                         => $isPropertyInitialized,
                        };

                        $handler->validate(
                            $value,
                            $constraint,
                        );
                    } else if ($isPropertyInitialized) {
                        /** @psalm-suppress PossiblyInvalidArgument */
                        $handler->validate($property->getValue($value), $constraint);
                    }

                    $violations->merge($handler->getViolations());
                }
            }
        }

        return $violations;
    }
}
