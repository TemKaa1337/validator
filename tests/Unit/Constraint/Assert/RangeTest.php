<?php

declare(strict_types=1);

namespace Constraint\Assert;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Temkaa\Validator\Constraint\Assert\Range;
use Temkaa\Validator\Exception\InvalidConstraintConfigurationException;

final class RangeTest extends TestCase
{
    /**
     * @return iterable<array{0: string|null, 1: string|null, 2: string|null, 3: string|null, 4: string}>
     */
    public static function getDataForValidationErrorTest(): iterable
    {
        yield [
            null,
            null,
            null,
            null,
            'Range constraint must have one of "min" or "max" argument set.',
        ];
        yield [
            1,
            2,
            null,
            null,
            'Range constraint must have one of "minMessage" or "maxMessage" argument set.',
        ];
        yield [
            2,
            1,
            'asd',
            'asd',
            'Argument "max" of Range constraint must be equal or greater than "min" value.',
        ];
        yield [
            -1,
            null,
            null,
            'test',
            'Range constraint must have both "min" and "minMessage" arguments set.',
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

        new Range($minLength, $maxLength, $minMessage, $maxMessage);
    }
}
