<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint;

use Temkaa\SimpleValidator\AbstractConstraintValidator;

interface ConstraintInterface
{
    /**
     * @template T of AbstractConstraintValidator
     *
     * @return class-string<T>
     */
    public function getHandler(): string;
}
