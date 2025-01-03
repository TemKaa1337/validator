<?php

declare(strict_types=1);

namespace Tests\Unit\Constraint\Validator;

use Countable;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Stringable;
use Temkaa\Validator\Constraint\Assert;
use Temkaa\Validator\Exception\UnexpectedTypeException;
use Temkaa\Validator\Validator;
use function sprintf;

final class LengthValidatorTest extends AbstractValidatorTestCase
{
    /**
     * @return iterable<array{0: object, 1: array<int, mixed>, 1: int}>
     */
    public static function getDataForInvalidTest(): iterable
    {
        $object = new class {
            #[Assert\Length(minLength: 1, minMessage: 'validation exception')]
            public string $test = '';
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
            #[Assert\Length(maxLength: 1, maxMessage: 'validation exception')]
            public string $test = 'aa';
        };
        yield [
            $object,
            [
                [
                    'message'      => 'validation exception',
                    'invalidValue' => 'aa',
                    'path'         => $object::class.'.test',
                ],
            ],
            1,
        ];

        $object = new class {
            #[Assert\Length(minLength: 1, minMessage: 'validation exception')]
            public array $test = [];
        };
        yield [
            $object,
            [
                [
                    'message'      => 'validation exception',
                    'invalidValue' => [],
                    'path'         => $object::class.'.test',
                ],
            ],
            1,
        ];

        $object = new class {
            #[Assert\Length(maxLength: 1, maxMessage: 'validation exception')]
            public array $test = ['test', 'test'];
        };
        yield [
            $object,
            [
                [
                    'message'      => 'validation exception',
                    'invalidValue' => ['test', 'test'],
                    'path'         => $object::class.'.test',
                ],
            ],
            1,
        ];

        $countable = new class implements Countable {
            public function count(): int
            {
                return 0;
            }
        };
        $object = new readonly class ($countable) {
            public function __construct(
                #[Assert\Length(minLength: 1, minMessage: 'validation exception')]
                public Countable $test,
            ) {
            }
        };
        yield [
            $object,
            [
                [
                    'message'      => 'validation exception',
                    'invalidValue' => $countable,
                    'path'         => $object::class.'.test',
                ],
            ],
            1,
        ];

        $countable = new class implements Countable {
            public function count(): int
            {
                return 2;
            }
        };
        $object = new readonly class ($countable) {
            public function __construct(
                #[Assert\Length(maxLength: 1, maxMessage: 'validation exception')]
                public Countable $test,
            ) {
            }
        };
        yield [
            $object,
            [
                [
                    'message'      => 'validation exception',
                    'invalidValue' => $countable,
                    'path'         => $object::class.'.test',
                ],
            ],
            1,
        ];

        $stringable = new class implements Stringable {
            public function __toString(): string
            {
                return '';
            }
        };
        $object = new readonly class ($stringable) {
            public function __construct(
                #[Assert\Length(minLength: 1, minMessage: 'validation exception')]
                public Stringable $test,
            ) {
            }
        };
        yield [
            $object,
            [
                [
                    'message'      => 'validation exception',
                    'invalidValue' => $stringable,
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
            #[Assert\Length(minLength: 1, minMessage: 'validation exception')]
            public string $test = 'a';
        };
        yield [$object];

        $object = new class {
            #[Assert\Length(
                minLength: 1,
                maxLength: 2,
                minMessage: 'validation exception',
                maxMessage: 'validation exception'
            )]
            public string $test = 'aa';
        };
        yield [$object];

        $object = new class {
            #[Assert\Length(maxLength: 2, maxMessage: 'validation exception')]
            public string $test = 'aa';
        };
        yield [$object];

        $object = new class {
            #[Assert\Length(minLength: 1, minMessage: 'validation exception')]
            public array $test = ['test1'];
        };
        yield [$object];

        $object = new class {
            #[Assert\Length(maxLength: 1, maxMessage: 'validation exception')]
            public array $test = ['test1'];
        };
        yield [$object];

        $object = new class {
            #[Assert\Length(
                minLength: 1,
                maxLength: 2,
                minMessage: 'validation exception',
                maxMessage: 'validation exception'
            )]
            public array $test = ['test1'];
        };
        yield [$object];

        $countable = new class implements Countable {
            public function count(): int
            {
                return 1;
            }
        };
        $object = new readonly class ($countable) {
            public function __construct(
                #[Assert\Length(minLength: 1, minMessage: 'validation exception')]
                public Countable $test,
            ) {
            }
        };
        yield [$object];

        $countable = new class implements Countable {
            public function count(): int
            {
                return 1;
            }
        };
        $object = new readonly class ($countable) {
            public function __construct(
                #[Assert\Length(maxLength: 1, maxMessage: 'validation exception')]
                public Countable $test,
            ) {
            }
        };
        yield [$object];

        $countable = new class implements Countable {
            public function count(): int
            {
                return 1;
            }
        };
        $object = new readonly class ($countable) {
            public function __construct(
                #[Assert\Length(
                    minLength: 1,
                    maxLength: 2,
                    minMessage: 'validation exception',
                    maxMessage: 'validation exception'
                )]
                public Countable $test,
            ) {
            }
        };
        yield [$object];

        $stringable = new class implements Stringable {
            public function __toString(): string
            {
                return 'a';
            }
        };
        $object = new readonly class ($stringable) {
            public function __construct(
                #[Assert\Length(minLength: 1, minMessage: 'validation exception')]
                public Stringable $test,
            ) {
            }
        };
        yield [$object];
    }

    /**
     * @return iterable<array{0: object, 1: string, 1: string}>
     */
    public static function getDataForValidateWithUnsupportedValueTypeTest(): iterable
    {
        $object = new class {
            #[Assert\Length(minLength: 1, minMessage: '')]
            public bool $test = true;
        };
        yield [
            $object,
            UnexpectedTypeException::class,
            sprintf(
                'Unexpected argument type exception, expected "%s" but got "%s".',
                'array|\Countable|string|\Stringable',
                'boolean',
            ),
        ];

        $object = new class {
            #[Assert\Length(minLength: 1, minMessage: '')]
            public int $test = 1;
        };
        yield [
            $object,
            UnexpectedTypeException::class,
            sprintf(
                'Unexpected argument type exception, expected "%s" but got "%s".',
                'array|\Countable|string|\Stringable',
                'integer',
            ),
        ];

        $object = new class {
            #[Assert\Length(minLength: 1, minMessage: '')]
            public float $test = 1.1;
        };
        yield [
            $object,
            UnexpectedTypeException::class,
            sprintf(
                'Unexpected argument type exception, expected "%s" but got "%s".',
                'array|\Countable|string|\Stringable',
                'double',
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

            #[Assert\Length(minLength: 1, minMessage: 'validation exception')]
            public string $value;
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
