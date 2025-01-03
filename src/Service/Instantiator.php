<?php

declare(strict_types=1);

namespace Temkaa\Validator\Service;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use Temkaa\Validator\Constraint\ConstraintValidatorInterface;
use Temkaa\Validator\Exception\CannotInstantiateValidatorException;
use function sprintf;

/**
 * @internal
 */
final readonly class Instantiator
{
    public function __construct(
        private ?ContainerInterface $container = null,
    ) {
    }

    /**
     * @psalm-suppress MixedInferredReturnType
     *
     * @template T of ConstraintValidatorInterface
     * @param class-string<T> $className
     *
     * @return T
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function instantiate(string $className): object
    {
        if ($this->container?->has($className)) {
            /** @phpstan-ignore return.type */
            return $this->container->get($className);
        }

        $reflection = $this->getClassReflection($className);

        $constructor = $reflection->getConstructor();
        $resolvedArguments = array_map(
            fn (ReflectionParameter $parameter): mixed => $this->resolveParameter($parameter, $className),
            $constructor?->getParameters() ?? [],
        );

        return $reflection->newInstanceArgs($resolvedArguments);
    }

    /**
     * @template T of ConstraintValidatorInterface
     * @param class-string<T> $className
     *
     * @return ReflectionClass<T>
     */
    private function getClassReflection(string $className): ReflectionClass
    {
        if (!class_exists($className)) {
            throw new CannotInstantiateValidatorException(
                message: sprintf('Cannot instantiate validator "%s" as this class does not exist.', $className),
            );
        }

        $reflection = new ReflectionClass($className);
        if (!$reflection->isInstantiable()) {
            throw new CannotInstantiateValidatorException(
                message: sprintf('Cannot instantiate validator "%s" as it is not instantiable.', $className),
            );
        }

        /** @psalm-suppress TypeDoesNotContainType */
        if (!$reflection->implementsInterface(ConstraintValidatorInterface::class)) {
            throw new CannotInstantiateValidatorException(
                message: sprintf(
                    'Cannot instantiate validator "%s" as it does not implement "%s" interface.',
                    $className,
                    ConstraintValidatorInterface::class,
                ),
            );
        }

        return $reflection;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function resolveParameter(ReflectionParameter $parameter, string $className): mixed
    {
        $parameterType = $parameter->getType();
        $parameterName = $parameter->getName();

        if (!$parameterType instanceof ReflectionNamedType) {
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }

            $message = $parameterType === null
                ? sprintf(
                    'Cannot instantiate validator "%s" with argument "%s" as it does not have a type.',
                    $className,
                    $parameterName,
                )
                : sprintf(
                    'Cannot instantiate validator "%s" with argument "%s" as its type is not concrete - "%s".',
                    $className,
                    $parameterName,
                    $parameterType,
                );

            throw new CannotInstantiateValidatorException($message);
        }

        if ($parameterType->isBuiltin()) {
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }

            throw new CannotInstantiateValidatorException(
                sprintf(
                    'Cannot instantiate validator "%s" with argument "%s" as its type is built-in.',
                    $className,
                    $parameterName,
                ),
            );
        }

        $parameterTypeName = $parameterType->getName();
        if ($this->container?->has($parameterTypeName)) {
            return $this->container->get($parameterTypeName);
        }

        throw new CannotInstantiateValidatorException(
            sprintf(
                'Cannot instantiate validator "%s" with argument "%s:%s" as it does not exist in container and cannot be resolved.',
                $className,
                $parameterName,
                $parameterType->getName(),
            ),
        );
    }
}
