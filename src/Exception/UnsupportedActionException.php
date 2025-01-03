<?php

declare(strict_types=1);

namespace Temkaa\Validator\Exception;

use InvalidArgumentException;

/**
 * @api
 */
final class UnsupportedActionException extends InvalidArgumentException implements ValidatorExceptionInterface
{
}
