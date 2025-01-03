<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use Temkaa\Container\Builder\ContainerBuilder;
use Temkaa\Validator\DependencyInjection\ConfigProvider;
use Temkaa\Validator\Validator;
use Temkaa\Validator\ValidatorInterface;

final class ContainerTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testBootsWithContainer(): void
    {
        $container = ContainerBuilder::make()->add(new ConfigProvider())->build();

        $validator = $container->get(ValidatorInterface::class);
        self::assertSame($validator, $container->get(Validator::class));

        $validatorReflector = new ReflectionClass($validator);
        $instantiator = $validatorReflector->getProperty('instantiator')->getValue($validator);

        $instantiatorReflector = new ReflectionClass($instantiator);
        $instantiatorContainer = $instantiatorReflector->getProperty('container')->getValue($instantiator);

        self::assertSame($container, $instantiatorContainer);
    }
}
