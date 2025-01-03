<?php

declare(strict_types=1);

namespace Tests\Helper\Stub;

use Attribute;
use Temkaa\Validator\Constraint\ConstraintInterface;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
final readonly class CustomConstraint implements ConstraintInterface
{
    public function getHandler(): string
    {
        return CustomValidator::class;
    }
}
