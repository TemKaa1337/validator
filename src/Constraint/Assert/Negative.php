<?php

declare(strict_types=1);

namespace Temkaa\Validator\Constraint\Assert;

use Attribute;
use Temkaa\Validator\Constraint\ConstraintInterface;
use Temkaa\Validator\Constraint\Validator\NegativeValidator;

/**
 * @api
 *
 * @template-implements ConstraintInterface<NegativeValidator>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Negative implements ConstraintInterface
{
    public function __construct(
        public string $message,
    ) {
    }

    public function getHandler(): string
    {
        return NegativeValidator::class;
    }
}
