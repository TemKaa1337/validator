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
use Temkaa\SimpleValidator\Constraint\Validator\CascadeValidator;
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
        $this->instantiator = new Instantiator($container);
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

        return $this->validateCollection($values, $constraints);
    }

    private function getErrorPathPrefix(
        ?string $errorPathPrefix,
        bool $isIterable,
        int $listIndex,
        string $className,
    ): string {
        /** @psalm-suppress RiskyTruthyFalsyComparison */
        return match (true) {
            $errorPathPrefix && $isIterable   => sprintf('%s[%s]', $errorPathPrefix, $listIndex),
            $errorPathPrefix && !$isIterable  => $errorPathPrefix,
            !$errorPathPrefix && $isIterable  => "[$listIndex]",
            !$errorPathPrefix && !$isIterable => $className,
        };
    }

    /**
     * @param iterable<object>|object $values
     * @param ConstraintInterface[]   $constraints
     * @param ?string                 $errorPathPrefix
     *
     * @return ViolationListInterface
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    private function validateCollection(
        iterable|object $values,
        array $constraints,
        ?string $errorPathPrefix = null,
    ): ViolationListInterface {
        $violations = new ViolationList();

        $isIterable = is_iterable($values);
        $values = $isIterable ? $values : [$values];

        if ($constraints) {
            foreach ($values as $value) {
                $validatedValue = new ValidatedValue($value, path: $value::class, isInitialized: true);

                $violations->merge($this->validateItem($validatedValue, $constraints));
            }

            return $violations;
        }

        foreach ($values as $index => $value) {
            $reflection = new ReflectionClass($value);

            $path = $this->getErrorPathPrefix($errorPathPrefix, $isIterable, $index, $value::class);

            $attributes = $reflection->getAttributes(
                ConstraintInterface::class,
                ReflectionAttribute::IS_INSTANCEOF,
            );
            foreach ($attributes as $attribute) {
                $validatedValue = new ValidatedValue($value, $path, isInitialized: true);

                $violations->merge($this->validateItem($validatedValue, $attribute->newInstance()));
            }

            $properties = $reflection->getProperties();
            foreach ($properties as $property) {
                $attributes = $property->getAttributes(ConstraintInterface::class, ReflectionAttribute::IS_INSTANCEOF);
                $errorPath = sprintf('%s.%s', $path, $property->getName());

                foreach ($attributes as $attribute) {
                    $isPropertyInitialized = $property->isInitialized($value);
                    $propertyValue = $isPropertyInitialized ? $property->getValue($value) : null;

                    $validatedValue = new ValidatedValue($propertyValue, $errorPath, $isPropertyInitialized);

                    $violations->merge($this->validateItem($validatedValue, $attribute->newInstance(), $errorPath));
                }
            }
        }

        return $violations;
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    private function validateItem(
        ValidatedValueInterface $validatedValue,
        array|ConstraintInterface $constraints,
        ?string $errorPathPrefix = null,
    ): ViolationListInterface {
        $violationList = new ViolationList();

        $constraints = is_array($constraints) ? $constraints : [$constraints];
        foreach ($constraints as $constraint) {
            $handler = $this->instantiator->instantiate($constraint->getHandler());

            $handler->validate($validatedValue, $constraint);

            $errors = $handler->getViolations();

            if ($handler::class === CascadeValidator::class) {
                if (!$validatedValue->isInitialized()) {
                    return new ViolationList();
                }

                $errors = $this->validateCollection(
                    $validatedValue->getValue(),
                    constraints: [],
                    errorPathPrefix: $errorPathPrefix,
                );
            }

            $violationList->merge($errors);
        }

        return $violationList;
    }
}
