<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint\Assert;

use Attribute;
use Temkaa\SimpleValidator\AbstractConstraintValidator;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\Validator\LessThanValidator;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class LessThan implements ConstraintInterface
{
    /**
     * @psalm-api
     */
    public function __construct(
        public float|int $threshold,
        public string $message,
        public bool $allowEquality = false,
    ) {
    }

    public function getHandler(): AbstractConstraintValidator
    {
        return new LessThanValidator();
    }
}
