<?php

declare(strict_types=1);

namespace Temkaa\Validator\Constraint\Assert;

use Attribute;
use Temkaa\Validator\Constraint\ConstraintInterface;
use Temkaa\Validator\Constraint\Validator\CascadeValidator;

/**
 * @api
 *
 * @template-implements ConstraintInterface<CascadeValidator>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Cascade implements ConstraintInterface
{
    /**
     * @return string
     */
    public function getHandler(): string
    {
        return CascadeValidator::class;
    }
}
