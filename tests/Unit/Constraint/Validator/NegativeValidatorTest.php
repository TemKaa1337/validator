<?php

declare(strict_types=1);

namespace Tests\Unit\Constraint\Validator;

use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Temkaa\Validator\Constraint\Assert;
use Temkaa\Validator\Exception\UnexpectedTypeException;
use Temkaa\Validator\Validator;
use function sprintf;

final class NegativeValidatorTest extends AbstractValidatorTestCase
{
    /**
     * @return iterable<array{0: object, 1: array<int, mixed>, 2: int}>
     */
    public static function getDataForInvalidTest(): iterable
    {
        $object = new class {
            #[Assert\Negative(message: 'validation exception')]
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
            #[Assert\Negative(message: 'validation exception')]
            public int $test = 1;
        };
        yield [
            $object,
            [
                [
                    'message'      => 'validation exception',
                    'invalidValue' => 1,
                    'path'         => $object::class.'.test',
                ],
            ],
            1,
        ];

        $object = new class {
            #[Assert\Negative(message: 'validation exception')]
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
            #[Assert\Negative(message: 'validation exception')]
            public float $test = 0.1;
        };
        yield [
            $object,
            [
                [
                    'message'      => 'validation exception',
                    'invalidValue' => 0.1,
                    'path'         => $object::class.'.test',
                ],
            ],
            1,
        ];
    }

    /**
     * @return iterable<array{0: object}>
     */
    public static function getDataForValidTest(): iterable
    {
        $object = new class {
            #[Assert\Negative(message: 'validation exception')]
            public int $test = -1;
        };
        yield [$object];

        $object = new class {
            #[Assert\Negative(message: 'validation exception')]
            public float $test = -1.1;
        };
        yield [$object];

        $object = new class {
            #[Assert\Negative(message: 'validation exception')]
            public float $test = -0.01;
        };
        yield [$object];
    }

    /**
     * @return iterable<array{0: object, 1: string, 2: string}>
     */
    public static function getDataForValidateWithUnsupportedValueTypeTest(): iterable
    {
        $object = new class {
            #[Assert\Negative(message: '')]
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
            #[Assert\Negative(message: '')]
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
            #[Assert\Negative(message: '')]
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

    #[DataProvider('getDataForInvalidTest')]
    public function testInvalid(object $value, array $invalidValuesInfo, int $expectedErrorsCount): void
    {
        parent::testInvalid($value, $invalidValuesInfo, $expectedErrorsCount);
    }

    /** @noinspection SenselessProxyMethodInspection */

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

    /**
     * @throws ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testValidateWithUninitializedValue(): void
    {
        $object = new class {
            #[Assert\Count(expected: 1, message: 'validation exception')]
            public array $test = ['test1'];

            #[Assert\Negative(message: 'validation exception')]
            public int $value;
        };

        $errors = (new Validator())->validate($object);

        $this->assertEmpty($errors);
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
