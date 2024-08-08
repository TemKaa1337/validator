<?php

declare(strict_types=1);

namespace Tests\Unit\Constraint\Validator;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use stdClass;
use Temkaa\SimpleValidator\Constraint\Assert;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\Validator\RangeValidator;
use Temkaa\SimpleValidator\Exception\InvalidConstraintConfigurationException;
use Temkaa\SimpleValidator\Exception\UnexpectedTypeException;
use Temkaa\SimpleValidator\Validator;
use Throwable;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class RangeValidatorTest extends AbstractValidatorTestCase
{
    public static function getDataForInvalidTest(): iterable
    {
        $object = new class {
            #[Assert\Range(min: 1, minMessage: 'validation exception')]
            public int $test = 0;
        };
        yield [$object, 0];

        $object = new class {
            #[Assert\Range(max: 1, maxMessage: 'validation exception')]
            public int $test = 2;
        };
        yield [$object, 2];

        $object = new class {
            #[Assert\Range(min: 1, max: 2, minMessage: 'validation exception', maxMessage: 'validation exception')]
            public int $test = 3;
        };
        yield [$object, 3];

        $object = new class {
            #[Assert\Range(min: 1.1, minMessage: 'validation exception')]
            public float $test = 1.09;
        };
        yield [$object, 1.09];

        $object = new class {
            #[Assert\Range(max: 1.1, maxMessage: 'validation exception')]
            public float $test = 1.11;
        };
        yield [$object, 1.11];

        $object = new class {
            #[Assert\Range(min: 1.1, max: 2.2, minMessage: 'validation exception', maxMessage: 'validation exception')]
            public float $test = 2.21;
        };
        yield [$object, 2.21];
    }

    public static function getDataForValidTest(): iterable
    {
        $object = new class {
            #[Assert\Range(min: 1, minMessage: 'validation exception')]
            public int $test = 1;
        };
        yield [$object];

        $object = new class {
            #[Assert\Range(max: 2, maxMessage: 'validation exception')]
            public int $test = 2;
        };
        yield [$object];

        $object = new class {
            #[Assert\Range(min: 1, max: 2, minMessage: 'validation exception', maxMessage: 'validation exception')]
            public int $test = 2;
        };
        yield [$object];

        $object = new class {
            #[Assert\Range(min: 1.0, minMessage: 'validation exception')]
            public float $test = 1.0;
        };
        yield [$object];

        $object = new class {
            #[Assert\Range(max: 2.1, maxMessage: 'validation exception')]
            public float $test = 2.1;
        };
        yield [$object];

        $object = new class {
            #[Assert\Range(min: 1.0, max: 2.1, minMessage: 'validation exception', maxMessage: 'validation exception')]
            public float $test = 2.1;
        };
        yield [$object];
    }

    public static function getDataForValidateWithInvalidConstraintSettingsTest(): iterable
    {
        yield [
            InvalidConstraintConfigurationException::class,
            'Length constraint must have one of "min" or "max" argument set.',
            new Assert\Range(),
        ];

        yield [
            InvalidConstraintConfigurationException::class,
            'Length constraint must have one of "min" or "max" argument set.',
            new Assert\Range(min: 1, max: 1),
        ];

        yield [
            InvalidConstraintConfigurationException::class,
            'Length constraint must have both "min" and "minMessage" arguments set.',
            new Assert\Range(min: 1, maxMessage: 'test'),
        ];

        yield [
            InvalidConstraintConfigurationException::class,
            'Length constraint must have both "max" and "maxMessage" arguments set.',
            new Assert\Range(min: 1, max: 1, minMessage: 'test'),
        ];

        yield [
            InvalidConstraintConfigurationException::class,
            'Argument "max" of Length constraint must be equal or greater than "min" value.',
            new Assert\Range(min: 1, max: 0, minMessage: 'test', maxMessage: 'test'),
        ];
    }

    public static function getDataForValidateWithUnsupportedValueTypeTest(): iterable
    {
        $object = new class {
            #[Assert\Range(min: 1, minMessage: '')]
            public bool $test = true;
        };
        yield [
            $object,
            UnexpectedTypeException::class,
            sprintf(
                'Unexpected argument type exception, expected "%s" but got "%s".',
                'float|int',
                'boolean',
            ),
        ];

        $object = new class {
            #[Assert\Range(min: 1, minMessage: '')]
            public array $test = [];
        };
        yield [
            $object,
            UnexpectedTypeException::class,
            sprintf(
                'Unexpected argument type exception, expected "%s" but got "%s".',
                'float|int',
                'array',
            ),
        ];

        $object = new class {
            /** @noinspection PropertyInitializationFlawsInspection */
            #[Assert\Range(min: 1, minMessage: '')]
            public null $test = null;
        };
        yield [
            $object,
            UnexpectedTypeException::class,
            sprintf(
                'Unexpected argument type exception, expected "%s" but got "%s".',
                'float|int',
                'NULL',
            ),
        ];
    }

    /**
     * @dataProvider getDataForInvalidTest
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testInvalid(object $value, mixed $invalidValue): void
    {
        $errors = (new Validator())->validate($value);

        $this->assertCount(1, $errors);

        foreach ($errors as $error) {
            self::assertEquals('validation exception', $error->getMessage());
            self::assertNull($error->getPath());
            self::assertEquals($invalidValue, $error->getInvalidValue());
        }
    }

    /**
     * @dataProvider getDataForValidTest
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testValid(object $value): void
    {
        $errors = (new Validator())->validate($value);

        /** @psalm-suppress TypeDoesNotContainType */
        $this->assertEmpty($errors);
    }

    public function testValidateInvalidConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Unexpected argument type exception, expected "%s" but got "%s".',
                Assert\Range::class,
                Assert\Count::class,
            ),
        );

        (new RangeValidator())->validate(new stdClass(), new Assert\Count(expected: 1, message: ''));
    }

    /**
     * @dataProvider getDataForValidateWithInvalidConstraintSettingsTest
     *
     * @param class-string<Throwable> $exception
     * @param string                  $exceptionMessage
     * @param ConstraintInterface     $constraint
     *
     * @return void
     */
    public function testValidateWithInvalidConstraintSettings(
        string $exception,
        string $exceptionMessage,
        ConstraintInterface $constraint,
    ): void {
        $this->expectException($exception);
        $this->expectExceptionMessage($exceptionMessage);

        (new RangeValidator())->validate(new stdClass(), $constraint);
    }

    /**
     * @dataProvider getDataForValidateWithUnsupportedValueTypeTest
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testValidateWithUnsupportedValueType(
        object $value,
        string $exception,
        string $exceptionMessage,
    ): void {
        $this->expectException($exception);
        $this->expectExceptionMessage($exceptionMessage);

        (new Validator())->validate($value);
    }
}
