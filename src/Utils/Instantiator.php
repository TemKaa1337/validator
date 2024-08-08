<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Utils;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use Temkaa\SimpleValidator\AbstractConstraintValidator;
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
     * @template T of AbstractConstraintValidator
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
        // TODO: fix all issues in tests
        $r = new ReflectionClass($className);
        if (!$r->isInstantiable()) {
            throw new CannotInstantiateValidatorException(
                message: sprintf('Cannot instantiate validator "%s" as it is not instantiable.', $className),
            );
        }

        $resolvedArguments = [];
        if (!$constructor = $r->getConstructor()) {
            return $r->newInstanceArgs($resolvedArguments);
        }

        $parameters = $constructor->getParameters();
        foreach ($parameters as $parameter) {
            $resolvedParameter = $this->resolveParameter($parameter, $className);

            $resolvedArguments[] = $resolvedParameter;
        }

        return $r->newInstanceArgs($resolvedArguments);
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
