<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint\Assert;

use Attribute;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\Validator\PositiveValidator;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Positive implements ConstraintInterface
{
    /**
     * @psalm-api
     */
    public function __construct(
        public string $message,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getHandler(): string
    {
        return PositiveValidator::class;
    }
}
