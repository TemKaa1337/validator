<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Service;

use ReflectionAttribute;
use ReflectionClass;
use Temkaa\SimpleValidator\Constraint\Assert\Cascade;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Model\ValidatedValue;
use Temkaa\SimpleValidator\Model\ValidatedValueInterface;

/**
 * @internal
 */
final class RuleCollector
{
    /**
     * @param iterable<object>|object $values
     * @param ConstraintInterface[]   $constraints
     * @param string|null             $errorPathPrefix
     *
     * @return list<array{0: ValidatedValueInterface, 1: ConstraintInterface[]}>
     */
    public function collect(
        iterable|object $values,
        array $constraints = [],
        ?string $errorPathPrefix = null,
    ): array {
        if ($rules = $this->collectFromConstraints($values, $constraints)) {
            return $rules;
        }

        return $this->collectFromClass($values, $errorPathPrefix);
    }

    /**
     * @param iterable<object>|object $values
     * @param string|null             $errorPathPrefix
     *
     * @return list<array{0: ValidatedValueInterface, 1: ConstraintInterface[]}>
     */
    private function collectFromClass(
        iterable|object $values,
        ?string $errorPathPrefix = null,
    ): array {
        $rules = [];

        $isIterable = is_iterable($values);
        $values = $isIterable ? $values : [$values];
        $index = 0;

        /** @var iterable<object> $values */
        foreach ($values as $value) {
            $reflection = new ReflectionClass($value);

            $errorPath = $this->getErrorPathPrefix($errorPathPrefix, $isIterable, $index, $value::class);

            $attributes = $reflection->getAttributes(
                ConstraintInterface::class,
                ReflectionAttribute::IS_INSTANCEOF,
            );
            foreach ($attributes as $attribute) {
                $rules[] = [
                    new ValidatedValue($value, $errorPath, isInitialized: true),
                    [$attribute->newInstance()],
                ];
            }

            // TODO: check if in tests there will be less assertions if override from parent class
            $rules = array_merge($rules, $this->collectFromProperties($reflection, $errorPath, $value));

            ++$index;
        }

        return $rules;
    }

    /**
     * @param iterable<object>|object $values
     * @param ConstraintInterface[]   $constraints
     *
     * @return list<array{0: ValidatedValueInterface, 1: ConstraintInterface[]}>
     */
    private function collectFromConstraints(
        iterable|object $values,
        array $constraints,
    ): array {
        if (!$constraints) {
            return [];
        }

        $rules = [];
        $values = is_iterable($values) ? $values : [$values];
        foreach ($values as $value) {
            $cascadeConstraintExists = array_filter(
                $constraints,
                static fn (ConstraintInterface $constraint): bool => $constraint instanceof Cascade,
            );

            if ($cascadeConstraintExists) {
                /** @psalm-suppress MixedArgument */
                $rules = array_merge($rules, $this->collect($value));
            } else {
                $rules[] = [
                    new ValidatedValue($value, path: $value::class, isInitialized: true),
                    $constraints,
                ];
            }
        }

        return $rules;
    }

    /**
     * @return list<array{0: ValidatedValueInterface, 1: ConstraintInterface[]}>
     */
    private function collectFromProperties(
        ReflectionClass $reflection,
        string $errorPathPrefix,
        object $value,
    ): array {
        $rules = [];

        $properties = $reflection->getProperties();
        foreach ($properties as $property) {
            $attributes = $property->getAttributes(ConstraintInterface::class, ReflectionAttribute::IS_INSTANCEOF);
            $errorPath = sprintf('%s.%s', $errorPathPrefix, $property->getName());

            foreach ($attributes as $attribute) {
                $isPropertyInitialized = $property->isInitialized($value);
                $propertyValue = $isPropertyInitialized ? $property->getValue($value) : null;
                $constraint = $attribute->newInstance();
                $validatedValue = new ValidatedValue($propertyValue, $errorPath, $isPropertyInitialized);

                if ($constraint instanceof Cascade) {
                    $handler = new ($constraint->getHandler());
                    $handler->validate($validatedValue, $constraint);

                    if ($isPropertyInitialized) {
                        /** @psalm-suppress MixedArgument, PossiblyNullArgument */
                        $rules = array_merge(
                            $rules,
                            $this->collect($propertyValue, errorPathPrefix: $errorPath),
                        );
                    }

                    continue;
                }

                $rules[] = [
                    new ValidatedValue($propertyValue, $errorPath, $isPropertyInitialized),
                    [$constraint],
                ];
            }
        }

        return $rules;
    }

    private function getErrorPathPrefix(
        ?string $errorPathPrefix,
        bool $isIterable,
        int $listIndex,
        string $className,
    ): string {
        return match (true) {
            $errorPathPrefix && $isIterable   => sprintf('%s[%s]', $errorPathPrefix, $listIndex),
            $errorPathPrefix && !$isIterable  => $errorPathPrefix,
            !$errorPathPrefix && $isIterable  => "[$listIndex]",
            !$errorPathPrefix && !$isIterable => $className,
        };
    }
}
