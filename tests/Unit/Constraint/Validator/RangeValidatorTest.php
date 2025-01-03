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

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class RangeValidatorTest extends AbstractValidatorTestCase
{
    /**
     * @return iterable<array{0: object, 1: array<int, mixed>, 2: int}>
     */
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

    /**
     * @return iterable<array{0: object}>
     */
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

    /**
     * @return iterable<array{0: object, 1: string, 2: string}>
     */
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

        $this->assertCount(0, $errors);
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

            #[Assert\Range(min: 1, max: 2, minMessage: 'validation exception', maxMessage: 'validation exception')]
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
