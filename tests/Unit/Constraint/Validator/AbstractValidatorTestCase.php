<?php

declare(strict_types=1);

namespace Tests\Unit\Constraint\Validator;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Temkaa\Validator\Validator;
use Throwable;

abstract class AbstractValidatorTestCase extends TestCase
{
    /**
     * @psalm-suppress MixedArrayAccess, MixedArrayOffset
     *
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     * @throws NotFoundExceptionInterface
     */
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
