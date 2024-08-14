<?php

declare(strict_types=1);

namespace Tests\Unit;

use Attribute;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use stdClass;
use Temkaa\SimpleValidator\AbstractConstraintValidator;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\ConstraintValidatorInterface;
use Temkaa\SimpleValidator\Constraint\Violation;
use Temkaa\SimpleValidator\Exception\CannotInstantiateValidatorException;
use Temkaa\SimpleValidator\Exception\UnexpectedTypeException;
use Temkaa\SimpleValidator\Exception\UnsupportedActionException;
use Temkaa\SimpleValidator\Model\ValidatedValueInterface;
use Temkaa\SimpleValidator\Validator;
use Tests\Unit\Stub\AbstractClass;
use Tests\Unit\Stub\ClassWithBuiltInParameterInConstructor;
use Tests\Unit\Stub\ClassWithBuiltInParameterInConstructorWithDefaultValue;
use Tests\Unit\Stub\ClassWithUnionConstructorType;
use Tests\Unit\Stub\ConstraintWithConfigurableHandler;
use Tests\Unit\Stub\CustomClass;
use Tests\Unit\Stub\CustomConstraint;
use Tests\Unit\Stub\CustomValidator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class ValidatorTest extends TestCase
{
    public static function getDataForValidateCustomConstraintWithContainerTest(): iterable
    {
        $testClass = new #[CustomConstraint] class {
            #[CustomConstraint]
            public string $string = 'string';
        };
        $container = new class implements ContainerInterface {
            public function get(string $id): object
            {
                return new CustomValidator(new CustomClass());
            }

            public function has(string $id): bool
            {
                return (bool) $id;
            }
        };
        yield [$testClass, $container];

        $testClass = new #[CustomConstraint] class {
            #[CustomConstraint]
            public string $string = 'string';
        };
        $container = new class implements ContainerInterface {
            public function get(string $id): object
            {
                return new CustomClass();
            }

            public function has(string $id): bool
            {
                return $id === CustomClass::class;
            }
        };
        yield [$testClass, $container];
    }

    public static function getDataForValidateWithUninstantiableValidatorTest(): iterable
    {
        $object = new stdClass();

        yield [
            $object,
            new ConstraintWithConfigurableHandler('nonexistent_class'),
            'Cannot instantiate validator "nonexistent_class" as this class does not exist.',
        ];
        yield [
            $object,
            new ConstraintWithConfigurableHandler(AbstractClass::class),
            sprintf('Cannot instantiate validator "%s" as it is not instantiable.', AbstractClass::class),
        ];
        yield [
            $object,
            new ConstraintWithConfigurableHandler(CustomClass::class),
            sprintf(
                'Cannot instantiate validator "%s" as it does not implement "%s" interface.',
                CustomClass::class,
                ConstraintValidatorInterface::class,
            ),
        ];
        yield [
            $object,
            new ConstraintWithConfigurableHandler(ClassWithUnionConstructorType::class),
            sprintf(
                'Cannot instantiate validator "%s" with argument "%s" as its type is not concrete - "%s".',
                ClassWithUnionConstructorType::class,
                'class',
                AbstractClass::class.'|'.CustomClass::class,
            ),
        ];
        yield [
            $object,
            new ConstraintWithConfigurableHandler(ClassWithBuiltInParameterInConstructor::class),
            sprintf(
                'Cannot instantiate validator "%s" with argument "%s" as its type is built-in.',
                ClassWithBuiltInParameterInConstructor::class,
                'value',
            ),
        ];
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    #[DataProvider('getDataForValidateCustomConstraintWithContainerTest')]
    public function testValidateCustomConstraintWithContainer(object $value, ContainerInterface $container): void
    {
        $errors = (new Validator($container))->validate($value);
        self::assertCount(2, $errors);

        $errors = iterator_to_array($errors);

        foreach ($errors as $index => $error) {
            self::assertEquals('message', $error->getMessage());
            self::assertEquals('path', $error->getPath());
            self::assertEquals($index === 0 ? $value : 'string', $error->getInvalidValue());
        }
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testValidateSpecificConstraint(): void
    {
        /** @psalm-suppress MissingConstructor */
        $object = new class {
            public string $test;
        };

        $constraintValidator = new class extends AbstractConstraintValidator {
            public function validate(ValidatedValueInterface $value, ConstraintInterface $constraint): void
            {
                $value = $value->getValue();
                if (!is_object($value)) {
                    throw new UnexpectedTypeException(actualType: gettype($value), expectedType: 'object');
                }

                $reflection = new ReflectionClass($value);
                foreach ($reflection->getProperties() as $property) {
                    if (!$property->isInitialized($value)) {
                        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
                        /** @psalm-suppress NoInterfaceProperties, MixedArgument */
                        $this->addViolation(
                            new Violation(
                                invalidValue: $value, message: $constraint->message, path: $property->getName(),
                            ),
                        );

                        return;
                    }
                }
            }
        };

        /** @psalm-suppress MissingConstructor, LessSpecificReturnStatement, MoreSpecificReturnType */
        $constraint = new #[Attribute(Attribute::TARGET_CLASS)] class implements ConstraintInterface {
            private string $handler;

            public string $message = 'failed';

            public function getHandler(): string
            {
                return $this->handler;
            }

            public function setHandler(string $handler): void
            {
                $this->handler = $handler;
            }
        };

        $constraint->setHandler($constraintValidator::class);

        $errors = (new Validator())->validate($object, $constraint);
        $this->assertCount(1, $errors);

        foreach ($errors as $error) {
            self::assertEquals('failed', $error->getMessage());
            self::assertEquals('test', $error->getPath());
            self::assertEquals($object, $error->getInvalidValue());
        }

        $errors = (new Validator())->validate($object, $constraint);
        $this->assertCount(1, $errors);

        foreach ($errors as $error) {
            self::assertEquals('failed', $error->getMessage());
            self::assertEquals('test', $error->getPath());
            self::assertEquals($object, $error->getInvalidValue());
        }
    }

    /**
     * @throws ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testValidateWithBuildInParameterInValidatorConstructorWithDefaultValue(): void
    {
        $object = new stdClass();
        $constraint = new ConstraintWithConfigurableHandler(
            ClassWithBuiltInParameterInConstructorWithDefaultValue::class,
        );

        $errors = (new Validator())->validate($object, $constraint);
        /** @psalm-suppress TypeDoesNotContainType */
        self::assertEmpty($errors);
    }

    /**
     * @throws ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testValidateWithConstraintNotImplementingConstraintInterface(): void
    {
        $object = new class {
        };
        $constraint = new class {
        };

        $this->expectException(UnsupportedActionException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot validate value with constraint of type "%s" as it does not implement "%s".',
                gettype($constraint),
                ConstraintInterface::class,
            ),
        );

        /** @psalm-suppress InvalidArgument */
        (new Validator())->validate($object, [$constraint]);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     * @throws NotFoundExceptionInterface
     */
    #[DataProvider('getDataForValidateWithUninstantiableValidatorTest')]
    public function testValidateWithUninstantiableValidator(
        object $value,
        ConstraintInterface $constraint,
        string $message,
    ): void {
        $this->expectException(CannotInstantiateValidatorException::class);
        $this->expectExceptionMessage($message);

        (new Validator())->validate($value, $constraint);
    }

    /**
     * @throws ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testValidateWithoutConstraints(): void
    {
        $object = new class {
        };
        $errors = (new Validator())->validate($object);

        /** @psalm-suppress TypeDoesNotContainType */
        self::assertEmpty($errors);
    }
}
