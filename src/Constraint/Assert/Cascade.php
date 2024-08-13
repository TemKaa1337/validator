<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint\Assert;

use Attribute;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\Validator\CascadeValidator;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Cascade implements ConstraintInterface
{
    /**
     * @inheritDoc
     */
    public function getHandler(): string
    {
        return CascadeValidator::class;
    }
}
