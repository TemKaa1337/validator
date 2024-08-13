<?php

declare(strict_types=1);

namespace Tests\Unit\Constraint\Validator;

use Countable;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use stdClass;
use Stringable;
use Temkaa\SimpleValidator\Constraint\Assert;
use Temkaa\SimpleValidator\Constraint\Validator\NotBlankValidator;
use Temkaa\SimpleValidator\Exception\UnexpectedTypeException;
use Temkaa\SimpleValidator\Model\ValidatedValue;
use Temkaa\SimpleValidator\Validator;

final class NotBlankValidatorTest extends AbstractValidatorTestCase
{
    public static function getDataForInvalidTest(): iterable
    {
        $object = new class {
            #[Assert\NotBlank(message: 'validation exception')]
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
            #[Assert\NotBlank(message: 'validation exception')]
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
            /** @noinspection PropertyInitializationFlawsInspection */
            #[Assert\NotBlank(message: 'validation exception')]
            public null $test = null;
        };
        yield [
            $object,
            [
                [
                    'message'      => 'validation exception',
                    'invalidValue' => null,
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
                #[Assert\NotBlank(message: 'validation exception')]
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
                #[Assert\NotBlank(message: 'validation exception')]
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

    public static function getDataForValidTest(): iterable
    {
        $object = new class {
            #[Assert\NotBlank(message: 'validation exception')]
            public string $test = 'a';
        };
        yield [$object];

        $object = new class {
            #[Assert\NotBlank(message: 'validation exception', allowNull: true)]
            public ?string $test = null;
        };
        yield [$object];

        $object = new class {
            #[Assert\NotBlank(message: 'validation exception')]
            public array $test = ['test'];
        };
        yield [$object];

        $object = new class {
            #[Assert\NotBlank(message: 'validation exception', allowNull: true)]
            public ?array $test = null;
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
                #[Assert\NotBlank(message: 'validation exception')]
                public Countable $test,
            ) {
            }
        };
        yield [$object];

        $stringable = new class implements Stringable {
            public function __toString(): string
            {
                return 'test';
            }
        };
        $object = new readonly class ($stringable) {
            public function __construct(
                #[Assert\NotBlank(message: 'validation exception')]
                public Stringable $test,
            ) {
            }
        };
        yield [$object];
    }

    public static function getDataForValidateWithUnsupportedValueTypeTest(): iterable
    {
        $object = new class {
            #[Assert\NotBlank(message: '')]
            public float $test = 1.11;
        };
        yield [
            $object,
            UnexpectedTypeException::class,
            sprintf(
                'Unexpected argument type exception, expected "%s" but got "%s".',
                'array|\Countable|string|\Stringable|null',
                'double',
            ),
        ];

        $object = new class {
            #[Assert\NotBlank(message: '')]
            public bool $test = true;
        };
        yield [
            $object,
            UnexpectedTypeException::class,
            sprintf(
                'Unexpected argument type exception, expected "%s" but got "%s".',
                'array|\Countable|string|\Stringable|null',
                'boolean',
            ),
        ];

        $object = new class {
            #[Assert\NotBlank(message: '')]
            public int $test = 1;
        };
        yield [
            $object,
            UnexpectedTypeException::class,
            sprintf(
                'Unexpected argument type exception, expected "%s" but got "%s".',
                'array|\Countable|string|\Stringable|null',
                'integer',
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
                Assert\NotBlank::class,
                Assert\Count::class,
            ),
        );

        (new NotBlankValidator())->validate(
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
