<?php

declare(strict_types=1);

namespace Temkaa\Validator\Constraint\Assert;

use Attribute;
use Temkaa\Validator\Constraint\ConstraintInterface;
use Temkaa\Validator\Constraint\Validator\GreaterThanValidator;

/**
 * @api
 *
 * @template-implements ConstraintInterface<GreaterThanValidator>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class GreaterThan implements ConstraintInterface
{
    public function __construct(
        public float|int $threshold,
        public string $message,
        public bool $allowEquality = false,
    ) {
    }

    public function getHandler(): string
    {
        return GreaterThanValidator::class;
    }
}
