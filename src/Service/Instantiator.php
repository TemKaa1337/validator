<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Service;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use Temkaa\SimpleValidator\Constraint\ConstraintValidatorInterface;
use Temkaa\SimpleValidator\Exception\CannotInstantiateValidatorException;

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
        // TODO: add tests on this
        // TODO: refactor validator
        // TODO: add Assert\Cascade (which will cascade validate everything)
        // TODO: add CORRECT invalid paths to constraint violations
        $reflection = $this->getClassReflection($className);

        $resolvedArguments = [];
        if (!$constructor = $reflection->getConstructor()) {
            return $reflection->newInstanceArgs($resolvedArguments);
        }

        $parameters = $constructor->getParameters();
        foreach ($parameters as $parameter) {
            $resolvedParameter = $this->resolveParameter($parameter, $className);

            $resolvedArguments[] = $resolvedParameter;
        }

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

        if (!$this->container) {
            if (!$parameter->isDefaultValueAvailable()) {
                throw new CannotInstantiateValidatorException(
                    sprintf(
                        'Cannot instantiate validator "%s" with argument "%s". '
                        .'Either instantiate "%s" with "%s" or provide a default value.',
                        $className,
                        $parameterName,
                        self::class,
                        ContainerInterface::class,
                    ),
                );
            }

            return $parameter->getDefaultValue();
        }

        if (!$parameterType instanceof ReflectionNamedType) {
            throw new CannotInstantiateValidatorException(
                sprintf(
                    'Cannot instantiate validator "%s" with argument "%s" as its type is "%s".',
                    $className,
                    $parameterName,
                    $parameterType ? (string) $parameterType : 'null',
                ),
            );
        }

        if ($parameterType->isBuiltin()) {
            throw new CannotInstantiateValidatorException(
                sprintf(
                    'Cannot instantiate validator "%s" with argument "%s" as its type is built-in.',
                    $className,
                    $parameterName,
                ),
            );
        }

        if (!$this->container->has($parameterName)) {
            throw new CannotInstantiateValidatorException(
                sprintf(
                    'Cannot instantiate validator "%s" with argument "%s:%s" as it does not exist in container.',
                    $className,
                    $parameterName,
                    $parameterType->getName(),
                ),
            );
        }

        return $this->container->get($parameterName);
    }
}
