<?php

declare(strict_types=1);

namespace Temkaa\Validator\Constraint\Assert;

use Attribute;
use Temkaa\Validator\Constraint\ConstraintInterface;
use Temkaa\Validator\Constraint\Validator\InitializedValidator;

/**
 * @api
 *
 * @template-implements ConstraintInterface<InitializedValidator>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Initialized implements ConstraintInterface
{
    public function __construct(
        public string $message,
    ) {
    }

    public function getHandler(): string
    {
        return InitializedValidator::class;
    }
}
