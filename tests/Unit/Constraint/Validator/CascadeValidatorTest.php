<?php

declare(strict_types=1);

namespace Tests\Unit\Constraint\Validator;

use ArrayIterator;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use stdClass;
use Temkaa\SimpleValidator\Constraint\Assert;
use Temkaa\SimpleValidator\Constraint\Validator\CascadeValidator;
use Temkaa\SimpleValidator\Exception\UnexpectedTypeException;
use Temkaa\SimpleValidator\Exception\UnsupportedActionException;
use Temkaa\SimpleValidator\Model\ValidatedValue;
use Temkaa\SimpleValidator\Validator;
use Tests\Unit\Stub\Cascade\ParentClass;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
                [
                    'message'      => 'validation exception 1',
                    'invalidValue' => ['test1'],
                    'path'         => $object::class.'.test',
                ],
                [
                    'message'      => 'validation exception 2',
                    'invalidValue' => 10,
                    'path'         => $object::class.'.arrayOfObjects[0].int',
                ],
                [
                    'message'      => 'validation exception 3',
                    'invalidValue' => -1,
                    'path'         => $object::class.'.arrayOfObjects[0].arrayOfObjects[0].int',
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
        yield [
            $object,
            [
                [
                    'message'      => 'validation exception 3',
                    'invalidValue' => -1,
                    'path'         => $object::class.'.iterableOfObjects[0].arrayOfObjects[0].int',
                ],
            ],
            1,
        ];

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
        yield [
            $object,
            [
                [
                    'message'      => 'validation exception 3',
                    'invalidValue' => -1,
                    'path'         => $object::class.'.object.object.int',
                ],
            ],
            1,
        ];
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
            UnsupportedActionException::class,
            'Cannot validate iterable<string> as the only supported types are object|iterable<object>.',
        ];

        $object = new class {
            #[Assert\Cascade]
            public bool $test = true;
        };
        yield [
            $object,
            UnsupportedActionException::class,
            'Cannot validate iterable<boolean> as the only supported types are object|iterable<object>.',
        ];

        $object = new class {
            /** @noinspection PropertyInitializationFlawsInspection */
            #[Assert\Cascade]
            public null $test = null;
        };
        yield [
            $object,
            UnsupportedActionException::class,
            'Cannot validate iterable<NULL> as the only supported types are object|iterable<object>.',
        ];

        $object = new class {
            #[Assert\Cascade]
            public array $test = ['string'];
        };
        yield [
            $object,
            UnsupportedActionException::class,
            'Cannot validate iterable<string> as the only supported types are object|iterable<object>.',
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
    public function testInvalidWithAppliedAsConstraint(): void
    {
        $object = new ParentClass();

        $errors = (new Validator())->validate($object, constraints: new Assert\Cascade());
        $this->assertCount(3, $errors);

        $errors = iterator_to_array($errors);

        self::assertEquals('ChildClass3 error', $errors[0]->getMessage());
        self::assertEquals(
            $object::class.'.class1.arrayOfObjects[0].iterableOfObjects[0].int',
            $errors[0]->getPath(),
        );
        self::assertEquals(-1, $errors[0]->getInvalidValue());

        self::assertEquals('ChildClass2 error', $errors[1]->getMessage());
        self::assertEquals($object::class.'.class1.arrayOfObjects[0].array', $errors[1]->getPath());
        self::assertEquals([1], $errors[1]->getInvalidValue());

        self::assertEquals('ChildClass1 error', $errors[2]->getMessage());
        self::assertEquals($object::class.'.class1.float', $errors[2]->getPath());
        self::assertEquals(1.01, $errors[2]->getInvalidValue());
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

        (new CascadeValidator())->validate(
            new ValidatedValue(new stdClass(), path: 'path', isInitialized: true),
            new Assert\Positive(message: ''),
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
