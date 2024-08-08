<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint\Assert;

use Attribute;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\Validator\CountValidator;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Count implements ConstraintInterface
{
    /**
     * @psalm-api
     */
    public function __construct(
        public int $expected,
        public string $message,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getHandler(): string
    {
        return CountValidator::class;
    }
}
