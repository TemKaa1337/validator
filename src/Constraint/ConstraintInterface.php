<?php

declare(strict_types=1);

namespace Temkaa\Validator\Constraint;

/**
 * @api
 *
 * @template TValidator of ConstraintValidatorInterface
 */
interface ConstraintInterface
{
    /**
     * @return class-string<TValidator>
     */
    public function getHandler(): string;
}
