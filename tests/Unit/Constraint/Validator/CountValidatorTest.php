<?php

declare(strict_types=1);

namespace Tests\Unit\Constraint\Validator;

use Countable;
use stdClass;
use Temkaa\SimpleValidator\Constraint\Assert;
use Temkaa\SimpleValidator\Constraint\Validator\CountValidator;
use Temkaa\SimpleValidator\Constraint\ViolationInterface;
use Temkaa\SimpleValidator\Exception\UnexpectedTypeException;
use Temkaa\SimpleValidator\Validator;

final class CountValidatorTest extends AbstractValidatorTestCase
{
    public static function getDataForInvalidTest(): iterable
    {
        $object = new class {
            #[Assert\Count(expected: 1, message: 'validation exception')]
            public array $test = ['test1', 'test2'];
        };
        yield [$object, ['test1', 'test2']];

        $object = new class {
            #[Assert\Count(expected: 1, message: 'validation exception')]
            public array $test = [];
        };
        yield [$object, []];

        $countable = new class implements Countable {
            public function count(): int
            {
                return 2;
            }
        };
        $object = new class ($countable) {
            public function __construct(
                #[Assert\Count(expected: 1, message: 'validation exception')]
                public readonly Countable $test,
            ) {
            }
        };
        yield [$object, $countable];
    }

    public static function getDataForValidTest(): iterable
    {
        $object = new class {
            #[Assert\Count(expected: 1, message: 'validation exception')]
            public array $test = ['test1'];
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
                #[Assert\Count(expected: 1, message: 'validation exception')]
                public readonly Countable $test,
            ) {
            }
        };
        yield [$object];
    }

    public static function getDataForValidateWithUnsupportedValueTypeTest(): iterable
    {
        $object = new class {
            #[Assert\Count(expected: 1, message: '')]
            public string $test = 'test';
        };
        yield [
            $object,
            UnexpectedTypeException::class,
            sprintf(
                'Unexpected argument type exception, expected "%s" but got "%s".',
                'array|\Countable',
                'string',
            ),
        ];

        $object = new class {
            #[Assert\Count(expected: 1, message: '')]
            public int $test = 1;
        };
        yield [
            $object,
            UnexpectedTypeException::class,
            sprintf(
                'Unexpected argument type exception, expected "%s" but got "%s".',
                'array|\Countable',
                'integer',
            ),
        ];

        $object = new class {
            #[Assert\Count(expected: 1, message: '')]
            public float $test = 1.1;
        };
        yield [
            $object,
            UnexpectedTypeException::class,
            sprintf(
                'Unexpected argument type exception, expected "%s" but got "%s".',
                'array|\Countable',
                'double',
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
                Assert\Count::class,
                Assert\Positive::class,
            ),
        );

        (new CountValidator())->validate(new stdClass(), new Assert\Positive(message: ''));
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
