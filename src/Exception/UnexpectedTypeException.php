<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Exception;

use InvalidArgumentException;

class UnexpectedTypeException extends InvalidArgumentException implements ValidatorExceptionInterface
{
    public function __construct(string $actualType, string $expectedType)
    {
        parent::__construct(
            sprintf(
                'Unexpected argument type exception, expected "%s" but got "%s".',
                $expectedType,
                $actualType,
            ),
        );
    }
}
