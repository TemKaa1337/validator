<?php

declare(strict_types=1);

namespace Tests\Unit\Constraint\Validator;

use stdClass;
use Temkaa\SimpleValidator\Constraint\Assert;
use Temkaa\SimpleValidator\Constraint\Validator\GreaterThanValidator;
use Temkaa\SimpleValidator\Constraint\ViolationInterface;
use Temkaa\SimpleValidator\Exception\UnexpectedTypeException;
use Temkaa\SimpleValidator\Validator;

final class GreaterThanValidatorTest extends AbstractValidatorTestCase
{
    public static function getDataForInvalidTest(): iterable
    {
        $object = new class {
            #[Assert\GreaterThan(threshold: 1, message: 'validation exception', allowEquality: true)]
            public int $test = 0;
        };
        yield [$object, 0];

        $object = new class {
            #[Assert\GreaterThan(threshold: 1, message: 'validation exception')]
            public int $test = 1;
        };
        yield [$object, 1];

        $object = new class {
            #[Assert\GreaterThan(threshold: 1, message: 'validation exception')]
            public float $test = 1.0;
        };
        yield [$object, 1.0];

        $object = new class {
            #[Assert\GreaterThan(threshold: 1, message: 'validation exception')]
            public float $test = -0.1;
        };
        yield [$object, -0.1];
    }

    public static function getDataForValidTest(): iterable
    {
        $object = new class {
            #[Assert\GreaterThan(threshold: 1, message: 'validation exception', allowEquality: true)]
            public int $test = 1;
        };
        yield [$object];

        $object = new class {
            #[Assert\GreaterThan(threshold: 1, message: 'validation exception')]
            public int $test = 2;
        };
        yield [$object];

        $object = new class {
            #[Assert\GreaterThan(threshold: 1, message: 'validation exception')]
            public float $test = 1.01;
        };
        yield [$object];

        $object = new class {
            #[Assert\GreaterThan(threshold: 1.01, message: 'validation exception', allowEquality: true)]
            public float $test = 1.01;
        };
        yield [$object];
    }

    public static function getDataForValidateWithUnsupportedValueTypeTest(): iterable
    {
        $object = new class {
            #[Assert\GreaterThan(threshold: 10, message: '')]
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
            #[Assert\GreaterThan(threshold: 10, message: '')]
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
            #[Assert\GreaterThan(threshold: 10, message: '')]
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
                Assert\GreaterThan::class,
                Assert\Positive::class,
            ),
        );

        (new GreaterThanValidator())->validate(new stdClass(), new Assert\Positive(message: ''));
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
