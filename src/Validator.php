<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use Temkaa\SimpleValidator\Constraint\Assert\Initialized;
use Temkaa\SimpleValidator\Constraint\Assert\NotBlank;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\Validator\CascadeValidator;
use Temkaa\SimpleValidator\Constraint\ViolationList;
use Temkaa\SimpleValidator\Constraint\ViolationListInterface;
use Temkaa\SimpleValidator\Exception\UninitializedPropertyException;
use Temkaa\SimpleValidator\Exception\UnsupportedActionException;
use Temkaa\SimpleValidator\Service\Instantiator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @psalm-api
 */
final readonly class Validator implements ValidatorInterface
{
    private Instantiator $instantiator;

    public function __construct(
        ?ContainerInterface $container = null,
    ) {
        $this->instantiator = new Instantiator($this, $container);
    }

    /**
     * @inheritDoc
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
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
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @param object                $value
     * @param ConstraintInterface[] $constraints
     *
     * @return ViolationListInterface
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    private function validateValue(object $value, array $constraints): ViolationListInterface
    {
        $violations = new ViolationList();

        // TODO: what if i want to validate array of objects OR iterable of objects
        // TODO: think about throwing exception if property is not initialized
        // and some other validator is proceeded (eg positive assert on property which is not initialized)
        $reflection = new ReflectionClass($value);
        if ($reflection->isInternal()) {
            return $violations;
        }

        if ($constraints) {
            foreach ($constraints as $constraint) {
                $handler = $this->instantiator->instantiate($constraint->getHandler());

                $handler->validate($value, $constraint);

                $violations->merge($handler->getViolations());
            }

            return $violations;
        }

        $attributes = $reflection->getAttributes();
        foreach ($attributes as $attribute) {
            $constraint = $attribute->newInstance();
            if (!$constraint instanceof ConstraintInterface) {
                continue;
            }

            $handler = $this->instantiator->instantiate($constraint->getHandler());

            $handler->validate($value, $constraint);

            $violations->merge($handler->getViolations());
        }

        $properties = $reflection->getProperties();
        foreach ($properties as $property) {
            $attributes = $property->getAttributes();

            foreach ($attributes as $attribute) {
                $constraint = $attribute->newInstance();

                if (!$constraint instanceof ConstraintInterface) {
                    continue;
                }

                $handler = $this->instantiator->instantiate($constraint->getHandler());

                $isPropertyInitialized = $property->isInitialized($value);

                if ($constraint instanceof Initialized || $constraint instanceof NotBlank) {
                    $propertyValue = match (true) {
                        $constraint instanceof NotBlank => $isPropertyInitialized
                            ? $property->getValue($value)
                            : '',
                        default                         => $isPropertyInitialized,
                    };

                    $handler->validate(
                        $propertyValue,
                        $constraint,
                    );
                } else {
                    if (!$isPropertyInitialized) {
                        throw new UninitializedPropertyException(
                            sprintf(
                                'Cannot read property value with name "%s" on object "%s".',
                                $property->getName(),
                                $value::class,
                            ),
                        );
                    }

                    $handler->validate($property->getValue($value), $constraint);
                }

                $violations->merge($handler->getViolations());
            }
        }

        return $violations;
    }
}
