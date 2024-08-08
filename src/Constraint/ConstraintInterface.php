<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint;

interface ConstraintInterface
{
    /**
     * @template T of ConstraintValidatorInterface
     *
     * @return class-string<T>
     */
    public function getHandler(): string;
}
