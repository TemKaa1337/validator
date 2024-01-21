<?php

declare(strict_types=1);

namespace Tests\Unit\Constraint\Validator;

use stdClass;
use Temkaa\SimpleValidator\Constraint\Assert;
use Temkaa\SimpleValidator\Constraint\Validator\InitializedValidator;
use Temkaa\SimpleValidator\Constraint\ViolationInterface;
use Temkaa\SimpleValidator\Exception\UnexpectedTypeException;
use Temkaa\SimpleValidator\Validator;

final class InitializedValidatorTest extends AbstractValidatorTestCase
{
    public static function getDataForInvalidTest(): iterable
    {
        $object = new class {
            #[Assert\Initialized(message: 'validation exception')]
            public int $test;
        };
        yield [$object, ''];

        $object = new class {
            #[Assert\Initialized(message: 'validation exception')]
            public string $test;
        };
        yield [$object, ''];

        $object = new class {
            #[Assert\Initialized(message: 'validation exception')]
            public bool $test;
        };
        yield [$object, ''];

        $object = new class {
            #[Assert\Initialized(message: 'validation exception')]
            public object $test;
        };
        yield [$object, ''];
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
                Assert\Initialized::class,
                Assert\Positive::class,
            ),
        );

        (new InitializedValidator())->validate(new stdClass(), new Assert\Positive(message: ''));
    }

    /**
     * @dataProvider getDataForValidateWithUnsupportedValueTypeTest
     */
    public function testValidateWithUnsupportedValueType(
        mixed $value,
        string $exception,
        string $exceptionMessage,
    ): void {
        $this->expectException($exception);
        $this->expectExceptionMessage($exceptionMessage);

        (new InitializedValidator())->validate($value, new Assert\Initialized(message: ''));
    }
}
