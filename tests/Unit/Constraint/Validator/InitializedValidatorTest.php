<?php

declare(strict_types=1);

namespace Tests\Unit\Constraint\Validator;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use stdClass;
use Temkaa\SimpleValidator\Constraint\Assert;
use Temkaa\SimpleValidator\Constraint\Validator\InitializedValidator;
use Temkaa\SimpleValidator\Exception\UnexpectedTypeException;
use Temkaa\SimpleValidator\Model\ValidatedValue;
use Temkaa\SimpleValidator\Validator;

final class InitializedValidatorTest extends AbstractValidatorTestCase
{
    public static function getDataForInvalidTest(): iterable
    {
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

    public static function getDataForValidateWithUnsupportedValueTypeTest(): iterable
    {
        yield [
            'string',
            UnexpectedTypeException::class,
            sprintf(
                'Unexpected argument type exception, expected "%s" but got "%s".',
                'boolean',
                'string',
            ),
        ];

        yield [
            1,
            UnexpectedTypeException::class,
            sprintf(
                'Unexpected argument type exception, expected "%s" but got "%s".',
                'boolean',
                'integer',
            ),
        ];

        yield [
            null,
            UnexpectedTypeException::class,
            sprintf(
                'Unexpected argument type exception, expected "%s" but got "%s".',
                'boolean',
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
    public function testInvalid(object $value, array $invalidValuesInfo, int $expectedErrorsCount): void
    {
        $errors = (new Validator())->validate($value);

        $this->assertCount($expectedErrorsCount, $errors);

        foreach ($errors as $index => $error) {
            self::assertEquals($invalidValuesInfo[$index]['message'], $error->getMessage());
            self::assertEquals($invalidValuesInfo[$index]['path'], $error->getPath());
            self::assertEquals($invalidValuesInfo[$index]['invalidValue'], $error->getInvalidValue());
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
                Assert\Initialized::class,
                Assert\Positive::class,
            ),
        );

        (new InitializedValidator())->validate(
            new ValidatedValue(new stdClass(), path: 'path', isInitialized: true),
            new Assert\Positive(message: ''),
        );
    }

    /**
     * @dataProvider getDataForValidateWithUnsupportedValueTypeTest
     */
    public function testValidateWithUnsupportedValueType(
        mixed $value,
        string $exception,
        string $exceptionMessage,
    ): void {
        $this->markTestSkipped(message: 'This validator does not have unsupported values.');
    }
}
