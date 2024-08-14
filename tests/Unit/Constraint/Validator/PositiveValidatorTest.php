<?php

declare(strict_types=1);

namespace Tests\Unit\Constraint\Validator;

use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use stdClass;
use Temkaa\SimpleValidator\Constraint\Assert;
use Temkaa\SimpleValidator\Constraint\Validator\PositiveValidator;
use Temkaa\SimpleValidator\Exception\UnexpectedTypeException;
use Temkaa\SimpleValidator\Model\ValidatedValue;
use Temkaa\SimpleValidator\Validator;

final class PositiveValidatorTest extends AbstractValidatorTestCase
{
    public static function getDataForInvalidTest(): iterable
    {
        $object = new class {
            #[Assert\Positive(message: 'validation exception')]
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
            #[Assert\Positive(message: 'validation exception')]
            public int $test = -1;
        };
        yield [
            $object,
            [
                [
                    'message'      => 'validation exception',
                    'invalidValue' => -1,
                    'path'         => $object::class.'.test',
                ],
            ],
            1,
        ];

        $object = new class {
            #[Assert\Positive(message: 'validation exception')]
            public float $test = 0.0;
        };
        yield [
            $object,
            [
                [
                    'message'      => 'validation exception',
                    'invalidValue' => 0.0,
                    'path'         => $object::class.'.test',
                ],
            ],
            1,
        ];

        $object = new class {
            #[Assert\Positive(message: 'validation exception')]
            public float $test = -0.1;
        };
        yield [
            $object,
            [
                [
                    'message'      => 'validation exception',
                    'invalidValue' => -0.1,
                    'path'         => $object::class.'.test',
                ],
            ],
            1,
        ];
    }

    public static function getDataForValidTest(): iterable
    {
        $object = new class {
            #[Assert\Positive(message: 'validation exception')]
            public int $test = 1;
        };
        yield [$object];

        $object = new class {
            #[Assert\Positive(message: 'validation exception')]
            public float $test = 1.1;
        };
        yield [$object];

        $object = new class {
            #[Assert\Positive(message: 'validation exception')]
            public float $test = 0.01;
        };
        yield [$object];
    }

    public static function getDataForValidateWithUnsupportedValueTypeTest(): iterable
    {
        $object = new class {
            #[Assert\Positive(message: '')]
            public string $test = 'test';
        };
        yield [
            $object,
            UnexpectedTypeException::class,
            sprintf(
                'Unexpected argument type exception, expected "%s" but got "%s".',
                'float|int',
                'string',
            ),
        ];

        $object = new class {
            #[Assert\Positive(message: '')]
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
            /** @noinspection PropertyInitializationFlawsInspection */
            #[Assert\Positive(message: '')]
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

    /** @noinspection SenselessProxyMethodInspection */
    #[DataProvider('getDataForInvalidTest')]
    public function testInvalid(object $value, array $invalidValuesInfo, int $expectedErrorsCount): void
    {
        parent::testInvalid($value, $invalidValuesInfo, $expectedErrorsCount);
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
                Assert\Positive::class,
                Assert\Count::class,
            ),
        );

        (new PositiveValidator())->validate(
            new ValidatedValue(new stdClass(), path: 'path', isInitialized: true),
            new Assert\Count(expected: 1, message: ''),
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
