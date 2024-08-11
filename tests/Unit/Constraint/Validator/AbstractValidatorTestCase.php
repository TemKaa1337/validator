<?php

declare(strict_types=1);

namespace Tests\Unit\Constraint\Validator;

use PHPUnit\Framework\TestCase;
use Throwable;

abstract class AbstractValidatorTestCase extends TestCase
{
    abstract public function testInvalid(object $value, mixed $invalidValue, int $expectedErrorsCount): void;

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
