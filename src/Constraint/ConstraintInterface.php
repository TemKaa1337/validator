<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint;

interface ConstraintInterface
{
    /**
     * @psalm-api
     *
     * @template T of ConstraintValidatorInterface
     *
     * @return class-string<T>
     */
    public function getHandler(): string;
}
