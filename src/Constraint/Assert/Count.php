<?php

declare(strict_types=1);

namespace Temkaa\Validator\Constraint\Assert;

use Attribute;
use Temkaa\Validator\Constraint\ConstraintInterface;
use Temkaa\Validator\Constraint\Validator\CountValidator;

/**
 * @api
 *
 * @template-implements ConstraintInterface<CountValidator>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Count implements ConstraintInterface
{
    public function __construct(
        public int $expected,
        public string $message,
    ) {
    }

    public function getHandler(): string
    {
        return CountValidator::class;
    }
}
