<?php

declare(strict_types=1);

namespace Tests\Unit\Constraint\Validator;

use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Temkaa\Validator\Constraint\Assert;
use Temkaa\Validator\Validator;

final class InitializedValidatorTest extends AbstractValidatorTestCase
{
    /**
     * @return iterable<array{0: object, 1: array<int, mixed>, 1: int}>
     */
    public static function getDataForInvalidTest(): iterable
    {
        /** @psalm-suppress MissingConstructor */
        $object = new class {
            #[Assert\Initialized(message: 'validation exception')]
            public int $test;
        };
        yield [
            $object,
            [
                [
                    'message'      => 'validation exception',
                    'invalidValue' => '',
                    'path'         => $object::class.'.test',
                ],
            ],
            1,
        ];

        /** @psalm-suppress MissingConstructor */
        $object = new class {
            #[Assert\Initialized(message: 'validation exception')]
            public string $test;
        };
        yield [
            $object,
            [
                [
                    'message'      => 'validation exception',
                    'invalidValue' => '',
                    'path'         => $object::class.'.test',
                ],
            ],
            1,
        ];

        $object = new class {
            #[Assert\Initialized(message: 'validation exception')]
            public bool $test;
        };
        yield [
            $object,
            [
                [
                    'message'      => 'validation exception',
                    'invalidValue' => '',
                    'path'         => $object::class.'.test',
                ],
            ],
            1,
        ];

        $object = new class {
            #[Assert\Initialized(message: 'validation exception')]
            public object $test;
        };
        yield [
            $object,
            [
                [
                    'message'      => 'validation exception',
                    'invalidValue' => '',
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
            #[Assert\Initialized(message: 'validation exception')]
            public int $test = 1;
        };
        yield [$object];

        $object = new class {
            #[Assert\Initialized(message: 'validation exception')]
            public float $test = 1.1;
        };
        yield [$object];

        $object = new class {
            #[Assert\Initialized(message: 'validation exception')]
            public bool $test = false;
        };
        yield [$object];
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testValidateWithUnsupportedValueType(
        mixed $value = null,
        string $exception = null,
        string $exceptionMessage = null,
    ): void {
        $this->markTestSkipped(message: 'This validator does not have unsupported values.');
    }
}
