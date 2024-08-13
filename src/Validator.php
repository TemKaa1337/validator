<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\ViolationList;
use Temkaa\SimpleValidator\Constraint\ViolationListInterface;
use Temkaa\SimpleValidator\Model\ValidatedValue;
use Temkaa\SimpleValidator\Model\ValidatedValueInterface;
use Temkaa\SimpleValidator\Service\Instantiator;
use Temkaa\SimpleValidator\Utils\InputArgumentValidator;

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
    public function validate(
        iterable|object $values,
        array|ConstraintInterface|null $constraints = null,
    ): ViolationListInterface {
        InputArgumentValidator::validateValues($values);
        InputArgumentValidator::validateConstraints($constraints);

        $constraints = match (true) {
            $constraints === null  => [],
            is_array($constraints) => $constraints,
            default                => [$constraints]
        };

        return $this->validateValues($values, $constraints);
    }

    /**
     * @param ValidatedValueInterface $value
     * @param ConstraintInterface[]   $constraints
     *
     * @return ViolationListInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    private function validateValue(ValidatedValueInterface $value, array $constraints): ViolationListInterface
    {
        $violations = new ViolationList();

        foreach ($constraints as $constraint) {
            $handler = $this->instantiator->instantiate($constraint->getHandler());

            $handler->validate($value, $constraint);

            $violations->merge($handler->getViolations());
        }

        return $violations;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @param iterable|object       $values
     * @param ConstraintInterface[] $constraints
     *
     * @return ViolationListInterface
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    private function validateValues(iterable|object $values, array $constraints): ViolationListInterface
    {
        $violations = new ViolationList();

        $isIterable = is_iterable($values);
        $values = $isIterable ? $values : [$values];

        if ($constraints) {
            foreach ($values as $value) {
                $validatedValue = new ValidatedValue($value, path: $value::class, isInitialized: true);

                $errors = $this->validateValue($validatedValue, $constraints);

                $violations->merge($errors);
            }

            return $violations;
        }

        foreach ($values as $index => $value) {
            $reflection = new ReflectionClass($value);
            if ($reflection->isInternal()) {
                continue;
            }

            $valueClassName = $value::class;
            $path = $isIterable ? "[$index].$valueClassName" : $valueClassName;

            $attributes = $reflection->getAttributes(
                ConstraintInterface::class,
                ReflectionAttribute::IS_INSTANCEOF,
            );
            foreach ($attributes as $attribute) {

                $validatedValue = new ValidatedValue($value, $path, isInitialized: true);

                $errors = $this->validateValue($validatedValue, [$attribute->newInstance()]);

                $violations->merge($errors);
            }

            $properties = $reflection->getProperties();
            foreach ($properties as $property) {
                $propertyName = $property->getName();
                $attributes = $property->getAttributes(ConstraintInterface::class, ReflectionAttribute::IS_INSTANCEOF);

                foreach ($attributes as $attribute) {
                    $isPropertyInitialized = $property->isInitialized($value);
                    $propertyValue = $isPropertyInitialized ? $property->getValue($value) : null;

                    $validatedValue = new ValidatedValue(
                        $propertyValue,
                        path: "$path.$propertyName",
                        isInitialized: $isPropertyInitialized,
                    );

                    $errors = $this->validateValue($validatedValue, [$attribute->newInstance()]);

                    $violations->merge($errors);
                }
            }
        }

        return $violations;
    }
}
