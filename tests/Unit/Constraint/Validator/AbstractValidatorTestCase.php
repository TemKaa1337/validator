<?php

declare(strict_types=1);

namespace Tests\Unit\Constraint\Validator;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Temkaa\SimpleValidator\Validator;
use Throwable;

abstract class AbstractValidatorTestCase extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     * @throws NotFoundExceptionInterface
     */
    #[DataProviderExternal(CascadeValidatorTest::class, 'getDataForInvalidTest')]
    #[DataProviderExternal(CountValidatorTest::class, 'getDataForInvalidTest')]
    #[DataProviderExternal(GreaterThanValidatorTest::class, 'getDataForInvalidTest')]
    #[DataProviderExternal(InitializedValidatorTest::class, 'getDataForInvalidTest')]
    #[DataProviderExternal(LengthValidatorTest::class, 'getDataForInvalidTest')]
    #[DataProviderExternal(LessThanValidatorTest::class, 'getDataForInvalidTest')]
    #[DataProviderExternal(NegativeValidatorTest::class, 'getDataForInvalidTest')]
    #[DataProviderExternal(NotBlankValidatorTest::class, 'getDataForInvalidTest')]
    #[DataProviderExternal(PositiveValidatorTest::class, 'getDataForInvalidTest')]
    #[DataProviderExternal(RangeValidatorTest::class, 'getDataForInvalidTest')]
    #[DataProviderExternal(RegexValidatorTest::class, 'getDataForInvalidTest')]
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

    abstract public function testValid(object $value): void;

    abstract public function testValidateInvalidConstraint(): void;

    /**
     * @param object                  $value
     * @param class-string<Throwable> $exception
     * @param string                  $exceptionMessage
     */
    abstract public function testValidateWithUnsupportedValueType(
        object $value,
        string $exception,
        string $exceptionMessage,
    ): void;
}
