<?php

declare(strict_types=1);

namespace Temkaa\Validator\Utils;

use Temkaa\Validator\Exception\UnsupportedActionException;
use function gettype;
use function is_iterable;
use function is_object;
use function sprintf;

/**
 * @internal
 */
final class InputArgumentValidator
{
    public function validateValues(mixed $values): void
    {
        if (is_object($values)) {
            return;
        }

        if (is_iterable($values)) {
            foreach ($values as $value) {
                if (!is_object($value)) {
                    throw new UnsupportedActionException(
                        sprintf(
                            'Cannot validate iterable<%s> as the only supported types are object|iterable<object>.',
                            gettype($value),
                        ),
                    );
                }
            }

            return;
        }

        throw new UnsupportedActionException(
            sprintf(
                'Cannot validate iterable<%s> as the only supported types are object|iterable<object>.',
                gettype($values),
            ),
        );
    }
}
