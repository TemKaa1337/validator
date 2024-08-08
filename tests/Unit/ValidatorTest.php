<?php

declare(strict_types=1);

namespace Tests\Unit;

use Attribute;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use Temkaa\SimpleValidator\AbstractConstraintValidator;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\Violation;
use Temkaa\SimpleValidator\Exception\UnexpectedTypeException;
use Temkaa\SimpleValidator\Validator;

final class ValidatorTest extends TestCase
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testValidateSpecificConstraint(): void
    {
        $object = new class {
            public string $test;
        };

        $constraintValidator = new class extends AbstractConstraintValidator {
            public function validate(mixed $value, ConstraintInterface $constraint): void
            {
                if (!is_object($value)) {
                    throw new UnexpectedTypeException(actualType: gettype($value), expectedType: 'object');
                }

                $r = new ReflectionClass($value);
                foreach ($r->getProperties() as $property) {
                    if (!$property->isInitialized($value)) {
                        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
                        /** @psalm-suppress NoInterfaceProperties */
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

        /** @psalm-suppress LessSpecificReturnStatement, MoreSpecificReturnType */
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
    public function testValidateWithoutConstraints(): void
    {
        $object = new class {
        };
        $errors = (new Validator())->validate($object);

        /** @psalm-suppress TypeDoesNotContainType */
        self::assertEmpty($errors);
    }
}
