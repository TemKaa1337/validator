<?php

declare(strict_types=1);

namespace Tests\Unit\Constraint\Validator;

use PHPUnit\Framework\TestCase;

abstract class AbstractValidatorTestCase extends TestCase
{
    abstract public function testInvalid(object $value, mixed $invalidValue): void;

    abstract public function testValid(object $value): void;

    abstract public function testValidateInvalidConstraint(): void;

    abstract public function testValidateWithUnsupportedValueType(
        object $value,
        string $exception,
        string $exceptionMessage,
    ): void;
}
