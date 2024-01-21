<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Exception;

use InvalidArgumentException;

final class InvalidConstraintConfigurationException extends InvalidArgumentException implements ValidatorExceptionInterface
{
}
