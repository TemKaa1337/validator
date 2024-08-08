<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Exception;

use LogicException;

final class UninitializedPropertyException extends LogicException implements ValidatorExceptionInterface
{
}
