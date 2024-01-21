<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint\Assert;

use Attribute;
use Temkaa\SimpleValidator\AbstractConstraintValidator;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\Validator\GreaterThanValidator;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class GreaterThan implements ConstraintInterface
{
    public function __construct(
        public float|int $threshold,
        public string $message,
        public bool $allowEquality = false,
    ) {
    }

    public function getHandler(): AbstractConstraintValidator
    {
        return new GreaterThanValidator();
    }
}
