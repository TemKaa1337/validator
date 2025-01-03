<?php

declare(strict_types=1);

namespace Temkaa\Validator\Service;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Temkaa\Validator\Constraint\Assert\Cascade;
use Temkaa\Validator\Constraint\ConstraintInterface;
use Temkaa\Validator\Model\ValidatedValue;
use Temkaa\Validator\Model\ValidatedValueInterface;
use function array_map;
use function array_merge;
use function sprintf;

/**
 * @internal
 */
final class RuleCollector
{
    /**
     * @template TConstraint of ConstraintInterface
     *
     * @param iterable<object>|object $values
     * @param list<TConstraint>       $constraints
     * @param string|null             $errorPathPrefix
     *
     * @return list<array{0: ValidatedValueInterface, 1: list<TConstraint>}>
     *
     * @throws ReflectionException
     */
    public function collect(
        iterable|object $values,
        array $constraints = [],
        ?string $errorPathPrefix = null,
    ): array {
        /** @var list<array{0: ValidatedValueInterface, 1: list<TConstraint>}> $rules */
        $rules = $this->collectFromConstraints($values, $constraints)
            ?: $this->collectFromClass($values, $errorPathPrefix);

        return $rules;
    }

    /**
     * @template TConstraint of ConstraintInterface
     *
     * @param iterable<object>|object   $values
     * @param class-string<TConstraint> $constraintBaseClass
     *
     * @return list<array{0: ValidatedValueInterface, 1: list<TConstraint>}>
     *
     * @throws ReflectionException
     */
    private function collectFromClass(
        iterable|object $values,
        ?string $errorPathPrefix = null,
        string $constraintBaseClass = ConstraintInterface::class,
    ): array {
        /** @var list<list<array{0: ValidatedValueInterface, 1: list<TConstraint>}>> $rules */
        $rules = [];

        $isIterable = is_iterable($values);
        $values = $isIterable ? $values : [$values];
        $index = 0;

        /** @var iterable<object> $values */
        foreach ($values as $value) {
            $reflection = new ReflectionClass($value);

            $errorPath = $this->getErrorPathPrefix($errorPathPrefix, $isIterable, $index, $value::class);

            /** @var list<TConstraint> $constraints */
            $constraints = $this->getAttributes($reflection, attributeClass: $constraintBaseClass);
            foreach ($constraints as $constraint) {
                /** @var list<array{0: ValidatedValueInterface, 1: list<TConstraint>}> $rule */
                $rule = [[new ValidatedValue($value, $errorPath, isInitialized: true), [$constraint]]];

                $rules[] = $rule;
            }

            /** @var list<array{0: ValidatedValueInterface, 1: list<TConstraint>}> $additionalRules */
            $additionalRules = $this->collectFromProperties($reflection, $errorPath, $value);

            $rules[] = $additionalRules;

            ++$index;
        }

        return array_merge(...$rules);
    }

    /**
     * @template TConstraint of ConstraintInterface
     *
     * @param iterable<object>|object $values
     * @param list<TConstraint>       $constraints
     *
     * @return list<array{0: ValidatedValueInterface, 1: list<TConstraint>}>
     *
     * @throws ReflectionException
     */
    private function collectFromConstraints(
        iterable|object $values,
        array $constraints,
    ): array {
        if (!$constraints) {
            return [];
        }

        /** @var list<list<array{0: ValidatedValueInterface, 1: list<TConstraint>}>> $rules */
        $rules = [];

        $values = is_iterable($values) ? $values : [$values];

        /** @var iterable<object>|list<object> $values */
        foreach ($values as $value) {
            $cascadeConstraintExists = array_filter(
                $constraints,
                static fn (ConstraintInterface $constraint): bool => $constraint instanceof Cascade,
            );

            $rules[] = $cascadeConstraintExists
                ? $this->collect($value)
                : [[new ValidatedValue($value, path: $value::class, isInitialized: true), $constraints]];
        }

        return array_merge(...$rules);
    }

    /**
     * @template TConstraint of ConstraintInterface
     * @template TObject of object
     *
     * @param ReflectionClass<TObject>  $reflection
     * @param TObject                   $value
     * @param class-string<TConstraint> $constraintBaseClass
     *
     * @return list<array{0: ValidatedValueInterface, 1: list<TConstraint>}>
     *
     * @throws ReflectionException
     */
    private function collectFromProperties(
        ReflectionClass $reflection,
        string $errorPathPrefix,
        object $value,
        string $constraintBaseClass = ConstraintInterface::class,
    ): array {
        /** @var list<list<array{0: ValidatedValueInterface, 1: list<TConstraint>}>> $rules */
        $rules = [];

        $properties = $reflection->getProperties();
        foreach ($properties as $property) {
            /** @var list<TConstraint> $constraints */
            $constraints = $this->getAttributes($property, attributeClass: $constraintBaseClass);
            $errorPath = sprintf('%s.%s', $errorPathPrefix, $property->getName());

            foreach ($constraints as $constraint) {
                $propertyInitialized = $property->isInitialized($value);
                $propertyValue = $propertyInitialized ? $property->getValue($value) : null;

                $validatedValue = new ValidatedValue($propertyValue, $errorPath, $propertyInitialized);

                if (!$constraint instanceof Cascade) {
                    $rules[] = [[$validatedValue, [$constraint]]];

                    continue;
                }

                $handler = new ($constraint->getHandler());
                $handler->validate($validatedValue, $constraint);

                if ($propertyInitialized) {
                    /** @var iterable<object>|object $propertyValue */
                    $rules[] = $this->collect($propertyValue, errorPathPrefix: $errorPath);
                }
            }
        }

        return array_merge(...$rules);
    }

    /**
     * @template TConstraint of ConstraintInterface
     * @template TObject of object
     *
     * @param ReflectionClass<TObject>|ReflectionProperty $reflector
     * @param class-string<TConstraint>                   $attributeClass
     *
     * @return list<TConstraint>
     */
    private function getAttributes(ReflectionClass|ReflectionProperty $reflector, string $attributeClass): array
    {
        return array_map(
            static fn (ReflectionAttribute $attribute): ConstraintInterface => $attribute->newInstance(),
            $reflector->getAttributes($attributeClass, ReflectionAttribute::IS_INSTANCEOF),
        );
    }

    private function getErrorPathPrefix(
        ?string $errorPathPrefix,
        bool $isIterable,
        int $listIndex,
        string $className,
    ): string {
        return match (true) {
            $errorPathPrefix && $isIterable  => "{$errorPathPrefix}[$listIndex]",
            $errorPathPrefix && !$isIterable => $errorPathPrefix,
            !$errorPathPrefix && $isIterable => "[$listIndex]",
            default                          => $className,
        };
    }
}
