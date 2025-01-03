<?php

declare(strict_types=1);

namespace Constraint\Assert;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Temkaa\Validator\Constraint\Assert\Length;
use Temkaa\Validator\Exception\InvalidConstraintConfigurationException;

final class LengthTest extends TestCase
{
    public static function getDataForValidationErrorTest(): iterable
    {
        yield [
            null,
            null,
            null,
            null,
            'Length constraint must have one of "minLength" or "maxLength" argument set.',
        ];
        yield [
            1,
            2,
            null,
            null,
            'Length constraint must have one of "minMessage" or "maxMessage" argument set.',
        ];
        yield [
            2,
            1,
            'asd',
            'asd',
            'Argument "maxLength" of Length constraint must be equal or greater than "minLength" value.',
        ];
        yield [
            -1,
            null,
            null,
            'test',
            'Length constraint must have both "minLength" and "minMessage" arguments set.',
        ];
        yield [
            -1,
            null,
            'test',
            null,
            'Argument "minLength" of Length constraint must be equal or greater than 0.',
        ];
        yield [
            null,
            -1,
            null,
            'test',
            'Argument "maxLength" of Length constraint must be equal or greater than 0.',
        ];
    }

    #[DataProvider('getDataForValidationErrorTest')]
    public function testWithValidationError(
        ?int $minLength,
        ?int $maxLength,
        ?string $minMessage,
        ?string $maxMessage,
        string $expectedErrorMessage,
    ): void {
        $this->expectException(InvalidConstraintConfigurationException::class);
        $this->expectExceptionMessage($expectedErrorMessage);

        new Length($minLength, $maxLength, $minMessage, $maxMessage);
    }
}
