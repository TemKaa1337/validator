<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Utils;

use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Exception\UnsupportedActionException;

/**
 * @internal
 */
final class InputArgumentValidator
{
    public static function validateConstraints(array|ConstraintInterface|null $constraints): void
    {
        if (!is_array($constraints)) {
            return;
        }

        foreach ($constraints as $constraint) {
            if (!$constraint instanceof ConstraintInterface) {
                throw new UnsupportedActionException(
                    sprintf(
                        'Cannot validate value with constraint of type "%s" as it does not implement "%s".',
                        gettype($constraint),
                        ConstraintInterface::class,
                    ),
                );
            }
        }
    }

    public static function validateValues(mixed $values): void
    {
        if (!is_iterable($values) && !is_object($values)) {
            throw new UnsupportedActionException(
                sprintf(
                    'Cannot validate iterable<%s> as the only supported types are object|iterable<object>.',
                    gettype($values),
                ),
            );
        }

        if (!is_iterable($values)) {
            return;
        }

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
    }
}
