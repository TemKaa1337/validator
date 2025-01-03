<?php

declare(strict_types=1);

namespace Temkaa\Validator\Exception;

use InvalidArgumentException;

/**
 * @api
 */
final class InvalidConstraintConfigurationException extends InvalidArgumentException implements ValidatorExceptionInterface
{
}
