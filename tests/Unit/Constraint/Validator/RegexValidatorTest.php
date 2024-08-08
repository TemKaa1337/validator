<?php

declare(strict_types=1);

namespace Tests\Unit\Constraint\Validator;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use stdClass;
use Stringable;
use Temkaa\SimpleValidator\Constraint\Assert;
use Temkaa\SimpleValidator\Constraint\Validator\RegexValidator;
use Temkaa\SimpleValidator\Exception\UnexpectedTypeException;
use Temkaa\SimpleValidator\Validator;

final class RegexValidatorTest extends AbstractValidatorTestCase
{
    public static function getDataForInvalidTest(): iterable
    {
        $object = new class {
            #[Assert\Regex(pattern: '/123/', message: 'validation exception')]
            public string $test = 'asd';
        };
        yield [$object, 'asd'];

        $stringable = new class implements Stringable {
            public function __toString(): string
            {
                return 'test';
            }
        };
        $object = new readonly class ($stringable) {
            public function __construct(
                #[Assert\Regex(pattern: '/123/', message: 'validation exception')]
                public Stringable $test,
            ) {
            }
        };
        yield [$object, $stringable];
    }

    public static function getDataForValidTest(): iterable
    {
        $object = new class {
            #[Assert\Regex(pattern: '/^[0-9]/', message: 'validation exception')]
            public string $test = '123';
        };
        yield [$object];

        $stringable = new class implements Stringable {
            public function __toString(): string
            {
                return '123';
            }
        };
        $object = new readonly class ($stringable) {
            public function __construct(
                #[Assert\Regex(pattern: '/^[0-9]/', message: 'validation exception')]
                public Stringable $test,
            ) {
            }
        };
        yield [$object];
    }

    public static function getDataForValidateWithUnsupportedValueTypeTest(): iterable
    {
        /** @psalm-suppress InvalidArgument */
        $object = new class {
            #[Assert\Regex(pattern: '', message: '')]
            public bool $test = true;
        };
        yield [
            $object,
            UnexpectedTypeException::class,
            sprintf(
                'Unexpected argument type exception, expected "%s" but got "%s".',
                'string|\Stringable',
                'boolean',
            ),
        ];

        /** @psalm-suppress InvalidArgument */
        $object = new class {
            #[Assert\Regex(pattern: '', message: '')]
            public array $test = [];
        };
        yield [
            $object,
            UnexpectedTypeException::class,
            sprintf(
                'Unexpected argument type exception, expected "%s" but got "%s".',
                'string|\Stringable',
                'array',
            ),
        ];

        /** @psalm-suppress InvalidArgument */
        $object = new class {
            /** @noinspection PropertyInitializationFlawsInspection */
            #[Assert\Regex(pattern: '', message: '')]
            public null $test = null;
        };
        yield [
            $object,
            UnexpectedTypeException::class,
            sprintf(
                'Unexpected argument type exception, expected "%s" but got "%s".',
                'string|\Stringable',
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
    public function testInvalid(object $value, mixed $invalidValue): void
    {
        $errors = (new Validator())->validate($value);

        $this->assertCount(1, $errors);

        foreach ($errors as $error) {
            self::assertEquals('validation exception', $error->getMessage());
            self::assertNull($error->getPath());
            self::assertEquals($invalidValue, $error->getInvalidValue());
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
                Assert\Regex::class,
                Assert\Count::class,
            ),
        );

        (new RegexValidator())->validate(new stdClass(), new Assert\Count(expected: 1, message: ''));
    }

    /**
     * @dataProvider getDataForValidateWithUnsupportedValueTypeTest
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
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
