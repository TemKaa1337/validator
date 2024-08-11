<?php

declare(strict_types=1);

namespace Tests\Unit\Constraint\Validator;

use ArrayIterator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use stdClass;
use Temkaa\SimpleValidator\Constraint\Assert;
use Temkaa\SimpleValidator\Constraint\Validator\CascadeValidator;
use Temkaa\SimpleValidator\Exception\UnexpectedTypeException;
use Temkaa\SimpleValidator\Validator;

/**
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
final class CascadeValidatorTest extends AbstractValidatorTestCase
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function getDataForInvalidTest(): iterable
    {
        $object = new class {
            #[Assert\Count(expected: 0, message: 'validation exception 1')]
            public array $test = ['test1'];

            #[Assert\Cascade]
            public array $arrayOfObjects;

            public function __construct()
            {
                $class1 = new class {
                    #[Assert\Negative(message: 'validation exception 2')]
                    public int $int = 10;

                    #[Assert\Cascade]
                    public array $arrayOfObjects;

                    public function __construct()
                    {
                        $class1 = new class {
                            #[Assert\Positive(message: 'validation exception 3')]
                            public int $int = -1;
                        };

                        $this->arrayOfObjects = [$class1];
                    }
                };
                $this->arrayOfObjects = [$class1];
            }
        };
        yield [
            $object,
            [
                'invalidValues' => [
                    [
                        'message'      => 'validation exception 1',
                        'invalidValue' => ['test1'],
                    ],
                    [
                        'message'      => 'validation exception 2',
                        'invalidValue' => 10,
                    ],
                    [
                        'message'      => 'validation exception 3',
                        'invalidValue' => -1,
                    ],
                ],
            ],
            3,
        ];

        $object = new class {
            #[Assert\Count(expected: 1, message: 'validation exception 1')]
            public array $test = ['test1'];

            #[Assert\Cascade]
            public iterable $iterableOfObjects;

            public function __construct()
            {
                $class1 = new class {
                    #[Assert\Positive(message: 'validation exception 2')]
                    public int $int = 10;

                    #[Assert\Cascade]
                    public array $arrayOfObjects;

                    public function __construct()
                    {
                        $class1 = new class {
                            #[Assert\Positive(message: 'validation exception 3')]
                            public int $int = -1;
                        };

                        $this->arrayOfObjects = [$class1];
                    }
                };
                $this->iterableOfObjects = new ArrayIterator([$class1]);
            }
        };
        yield [$object, -1, 1];

        $object = new class {
            #[Assert\Count(expected: 1, message: 'validation exception 1')]
            public array $test = ['test1'];

            #[Assert\Cascade]
            public object $object;

            public function __construct()
            {
                $class1 = new class {
                    #[Assert\Positive(message: 'validation exception 2')]
                    public int $int = 10;

                    #[Assert\Cascade]
                    public object $object;

                    public function __construct()
                    {
                        $class1 = new class {
                            #[Assert\Positive(message: 'validation exception 3')]
                            public int $int = -1;
                        };

                        $this->object = $class1;
                    }
                };
                $this->object = $class1;
            }
        };
        yield [$object, -1, 1];
    }

    public static function getDataForValidTest(): iterable
    {
        $object = new class {
            #[Assert\Count(expected: 1, message: 'validation exception')]
            public array $test = ['test1'];

            #[Assert\Cascade]
            public array $arrayOfObjects;

            public function __construct()
            {
                $class1 = new class {
                    #[Assert\Positive(message: 'validation exception')]
                    public int $int = 10;

                    #[Assert\Cascade]
                    public array $arrayOfObjects;

                    public function __construct()
                    {
                        $class1 = new class {
                            #[Assert\Negative(message: 'validation exception')]
                            public int $int = -1;
                        };

                        $this->arrayOfObjects = [$class1];
                    }
                };
                $this->arrayOfObjects = [$class1];
            }
        };
        yield [$object];

        $object = new class {
            #[Assert\Count(expected: 1, message: 'validation exception')]
            public array $test = ['test1'];

            #[Assert\Cascade]
            public iterable $iterableOfObjects;

            public function __construct()
            {
                $class1 = new class {
                    #[Assert\Positive(message: 'validation exception')]
                    public int $int = 10;

                    #[Assert\Cascade]
                    public array $arrayOfObjects;

                    public function __construct()
                    {
                        $class1 = new class {
                            #[Assert\Negative(message: 'validation exception')]
                            public int $int = -1;
                        };

                        $this->arrayOfObjects = [$class1];
                    }
                };
                $this->iterableOfObjects = new ArrayIterator([$class1]);
            }
        };
        yield [$object];

        $object = new class {
            #[Assert\Count(expected: 1, message: 'validation exception')]
            public array $test = ['test1'];

            #[Assert\Cascade]
            public object $object;

            public function __construct()
            {
                $class1 = new class {
                    #[Assert\Positive(message: 'validation exception')]
                    public int $int = 10;

                    #[Assert\Cascade]
                    public array $arrayOfObjects;

                    public function __construct()
                    {
                        $class1 = new class {
                            #[Assert\Negative(message: 'validation exception')]
                            public int $int = -1;
                        };

                        $this->arrayOfObjects = [$class1];
                    }
                };
                $this->object = $class1;
            }
        };
        yield [$object];
    }

    public static function getDataForValidateWithUnsupportedValueTypeTest(): iterable
    {
        $object = new class {
            #[Assert\Cascade]
            public string $test = 'test';
        };
        yield [
            $object,
            UnexpectedTypeException::class,
            sprintf(
                'Unexpected argument type exception, expected "%s" but got "%s".',
                'object|iterable',
                'string',
            ),
        ];

        $object = new class {
            #[Assert\Cascade]
            public bool $test = true;
        };
        yield [
            $object,
            UnexpectedTypeException::class,
            sprintf(
                'Unexpected argument type exception, expected "%s" but got "%s".',
                'object|iterable',
                'boolean',
            ),
        ];

        $object = new class {
            /** @noinspection PropertyInitializationFlawsInspection */
            #[Assert\Cascade]
            public null $test = null;
        };
        yield [
            $object,
            UnexpectedTypeException::class,
            sprintf(
                'Unexpected argument type exception, expected "%s" but got "%s".',
                'object|iterable',
                'NULL',
            ),
        ];

        $object = new class {
            #[Assert\Cascade]
            public array $test = ['string'];
        };
        yield [
            $object,
            UnexpectedTypeException::class,
            sprintf(
                'Unexpected argument type exception, expected "%s" but got "%s".',
                'object',
                'string',
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
    public function testInvalid(object $value, mixed $invalidValue, int $expectedErrorsCount): void
    {
        $errors = (new Validator())->validate($value);

        $this->assertCount($expectedErrorsCount, $errors);

        if (is_array($invalidValue) && array_key_exists('invalidValues', $invalidValue)) {
            foreach ($errors as $index => $error) {
                self::assertEquals($invalidValue['invalidValues'][$index]['message'], $error->getMessage());
                self::assertNull($error->getPath());
                self::assertEquals($invalidValue['invalidValues'][$index]['invalidValue'], $error->getInvalidValue());
            }
        } else {
            $errors = iterator_to_array($errors);
            self::assertEquals('validation exception 3', $errors[0]->getMessage());
            self::assertNull($errors[0]->getPath());
            self::assertEquals($invalidValue, $errors[0]->getInvalidValue());
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testInvalidWithAppliedAsConstraint(): void
    {
        $object = new class {
            #[Assert\Count(expected: 1, message: 'validation exception')]
            public array $test = ['test1'];

            #[Assert\Cascade]
            public array $arrayOfObjects;

            public function __construct()
            {
                $class1 = new class {
                    #[Assert\Positive(message: 'validation exception')]
                    public int $int = 10;

                    #[Assert\Cascade]
                    public array $arrayOfObjects;

                    public function __construct()
                    {
                        $class1 = new class {
                            #[Assert\Positive(message: 'validation exception 3')]
                            public int $int = -1;
                        };

                        $this->arrayOfObjects = [$class1];
                    }
                };
                $this->arrayOfObjects = [$class1];
            }
        };

        $errors = (new Validator())->validate($object, constraints: new Assert\Cascade());
        $this->assertCount(1, $errors);

        $error = iterator_to_array($errors)[0];
        self::assertEquals('validation exception 3', $error->getMessage());
        self::assertNull($error->getPath());
        self::assertEquals(-1, $error->getInvalidValue());
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

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testValidWithAppliedAsConstraint(): void
    {
        $object = new class {
            #[Assert\Count(expected: 1, message: 'validation exception')]
            public array $test = ['test1'];

            #[Assert\Cascade]
            public array $arrayOfObjects;

            public function __construct()
            {
                $class1 = new class {
                    #[Assert\Positive(message: 'validation exception')]
                    public int $int = 10;

                    #[Assert\Cascade]
                    public array $arrayOfObjects;

                    public function __construct()
                    {
                        $class1 = new class {
                            #[Assert\Negative(message: 'validation exception')]
                            public int $int = -1;
                        };

                        $this->arrayOfObjects = [$class1];
                    }
                };
                $this->arrayOfObjects = [$class1];
            }
        };

        $errors = (new Validator())->validate($object, constraints: new Assert\Cascade());
        /** @psalm-suppress TypeDoesNotContainType */
        $this->assertEmpty($errors);
    }

    public function testValidateInvalidConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Unexpected argument type exception, expected "%s" but got "%s".',
                Assert\Cascade::class,
                Assert\Positive::class,
            ),
        );

        (new CascadeValidator((new Validator())))->validate(new stdClass(), new Assert\Positive(message: ''));
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
