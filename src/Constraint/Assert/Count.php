<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint\Assert;

use Attribute;
use Temkaa\SimpleValidator\AbstractConstraintValidator;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\Validator\CountValidator;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Count implements ConstraintInterface
{
    public function __construct(
        public int $expected,
        public string $message,
    ) {
    }

    public function getHandler(): AbstractConstraintValidator
    {
        return new CountValidator();
    }
}
