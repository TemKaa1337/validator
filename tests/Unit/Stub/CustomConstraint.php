<?php

declare(strict_types=1);

namespace Tests\Unit\Stub;

use Attribute;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
final readonly class CustomConstraint implements ConstraintInterface
{
    public function getHandler(): string
    {
        return CustomValidator::class;
    }
}
