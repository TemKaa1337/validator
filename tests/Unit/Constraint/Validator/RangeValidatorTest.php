<?php

declare(strict_types=1);

namespace Tests\Unit\Constraint\Validator;

use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use stdClass;
use Temkaa\SimpleValidator\Constraint\Assert;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\Validator\RangeValidator;
use Temkaa\SimpleValidator\Exception\InvalidConstraintConfigurationException;
use Temkaa\SimpleValidator\Exception\UnexpectedTypeException;
use Temkaa\SimpleValidator\Model\ValidatedValue;
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
        yield [
            $object,
            [
                [
                    'message'      => 'validation exception',
                    'invalidValue' => 0,
                    'path'         => $object::class.'.test',
                ],
            ],
            1,
        ];

        $object = new class {
            #[Assert\Range(max: 1, maxMessage: 'validation exception')]
            public int $test = 2;
        };
        yield [
            $object,
            [
                [
                    'message'      => 'validation exception',
                    'invalidValue' => 2,
                    'path'         => $object::class.'.test',
                ],
            ],
            1,
        ];

        $object = new class {
            #[Assert\Range(min: 1, max: 2, minMessage: 'validation exception', maxMessage: 'validation exception')]
            public int $test = 3;
        };
        yield [
            $object,
            [
                [
                    'message'      => 'validation exception',
                    'invalidValue' => 3,
                    'path'         => $object::class.'.test',
                ],
            ],
            1,
        ];

        $object = new class {
            #[Assert\Range(min: 1.1, minMessage: 'validation exception')]
            public float $test = 1.09;
        };
        yield [
            $object,
            [
                [
                    'message'      => 'validation exception',
                    'invalidValue' => 1.09,
                    'path'         => $object::class.'.test',
                ],
            ],
            1,
        ];

        $object = new class {
            #[Assert\Range(max: 1.1, maxMessage: 'validation exception')]
            public float $test = 1.11;
        };
        yield [
            $object,
            [
                [
                    'message'      => 'validation exception',
                    'invalidValue' => 1.11,
                    'path'         => $object::class.'.test',
                ],
            ],
            1,
        ];

        $object = new class {
            #[Assert\Range(min: 1.1, max: 2.2, minMessage: 'validation exception', maxMessage: 'validation exception')]
            public float $test = 2.21;
        };
        yield [
            $object,
            [
                [
                    'message'      => 'validation exception',
                    'invalidValue' => 2.21,
                    'path'         => $object::class.'.test',
                ],
            ],
            1,
        ];
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    #[DataProvider('getDataForValidTest')]
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

        (new RangeValidator())->validate(
            new ValidatedValue(new stdClass(), path: 'path', isInitialized: true),
            new Assert\Count(expected: 1, message: ''),
        );
    }

    /**
     * @param class-string<Throwable> $exception
     * @param string                  $exceptionMessage
     * @param ConstraintInterface     $constraint
     *
     * @return void
     */
    #[DataProvider('getDataForValidateWithInvalidConstraintSettingsTest')]
    public function testValidateWithInvalidConstraintSettings(
        string $exception,
        string $exceptionMessage,
        ConstraintInterface $constraint,
    ): void {
        $this->expectException($exception);
        $this->expectExceptionMessage($exceptionMessage);

        (new RangeValidator())->validate(
            new ValidatedValue(new stdClass(), path: 'path', isInitialized: true),
            $constraint,
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    #[DataProvider('getDataForValidateWithUnsupportedValueTypeTest')]
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
