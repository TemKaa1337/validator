<?php

declare(strict_types=1);

namespace Temkaa\Validator\Constraint\Assert;

use Attribute;
use Temkaa\Validator\Constraint\ConstraintInterface;
use Temkaa\Validator\Constraint\Validator\PositiveValidator;

/**
 * @api
 *
 * @template-implements ConstraintInterface<PositiveValidator>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Positive implements ConstraintInterface
{
    public function __construct(
        public string $message,
    ) {
    }

    public function getHandler(): string
    {
        return PositiveValidator::class;
    }
}
