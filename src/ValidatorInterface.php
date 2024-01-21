<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator;

use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\ViolationListInterface;

interface ValidatorInterface
{
    public function validate(object $value, array|ConstraintInterface|null $constraints = null): ViolationListInterface;
}
