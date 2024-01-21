<?php

declare(strict_types=1);

namespace Tests\Unit\Constraint\Validator;

use Countable;
use stdClass;
use Stringable;
use Temkaa\SimpleValidator\Constraint\Assert;
use Temkaa\SimpleValidator\Constraint\Validator\NotBlankValidator;
use Temkaa\SimpleValidator\Constraint\ViolationInterface;
use Temkaa\SimpleValidator\Exception\UnexpectedTypeException;
use Temkaa\SimpleValidator\Validator;

final class NotBlankValidatorTest extends AbstractValidatorTestCase
{
    public static function getDataForInvalidTest(): iterable
    {
        $object = new class {
            #[Assert\NotBlank(message: 'validation exception')]
            public string $test = '';
        };
        yield [$object, ''];

        $object = new class {
            #[Assert\NotBlank(message: 'validation exception')]
            public array $test = [];
        };
        yield [$object, []];

        $object = new class {
            #[Assert\NotBlank(message: 'validation exception')]
            public null $test = null;
        };
        yield [$object, null];

        $countable = new class implements Countable {
            public function count(): int
            {
                return 0;
            }
        };
        $object = new class ($countable) {
            public function __construct(
                #[Assert\NotBlank(message: 'validation exception')]
                public readonly Countable $test,
            ) {
            }
        };
        yield [$object, $countable];

        $stringable = new class implements Stringable {
            public function __toString(): string
            {
                return '';
            }
        };
        $object = new class ($stringable) {
            public function __construct(
                #[Assert\NotBlank(message: 'validation exception')]
                public readonly Stringable $test,
            ) {
            }
        };
        yield [$object, $stringable];
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
        $object = new class ($countable) {
            public function __construct(
                #[Assert\NotBlank(message: 'validation exception')]
                public readonly Countable $test,
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
        $object = new class ($stringable) {
            public function __construct(
                #[Assert\NotBlank(message: 'validation exception')]
                public readonly Stringable $test,
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
     * @dataProvider getDataForInvalidTest
     */
    public function testInvalid(object $value, mixed $invalidValue): void
    {
        $errors = (new Validator())->validate($value);

        $this->assertCount(1, $errors);
        /** @var ViolationInterface $error */
        foreach ($errors as $error) {
            self::assertEquals('validation exception', $error->getMessage());
            self::assertNull($error->getPath());
            self::assertEquals($invalidValue, $error->getInvalidValue());
        }
    }

    /**
     * @dataProvider getDataForValidTest
     */
    public function testValid(object $value): void
    {
        $errors = (new Validator())->validate($value);

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

        (new NotBlankValidator())->validate(new stdClass(), new Assert\Count(expected: 1, message: ''));
    }

    /**
     * @dataProvider getDataForValidateWithUnsupportedValueTypeTest
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
