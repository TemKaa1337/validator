<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint;

use Temkaa\SimpleValidator\AbstractConstraintValidator;

interface ConstraintInterface
{
    public function getHandler(): AbstractConstraintValidator;
}
