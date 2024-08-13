<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint\Assert;

use Attribute;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\Validator\GreaterThanValidator;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class GreaterThan implements ConstraintInterface
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

    /**
     * @inheritDoc
     */
    public function getHandler(): string
    {
        return GreaterThanValidator::class;
    }
}
