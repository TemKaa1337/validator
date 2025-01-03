<?php

declare(strict_types=1);

namespace Temkaa\Validator\Exception;

use InvalidArgumentException;

/**
 * @api
 */
final class CannotInstantiateValidatorException extends InvalidArgumentException implements ValidatorExceptionInterface
{
}
