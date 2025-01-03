<?php

declare(strict_types=1);

namespace Temkaa\Validator\Constraint\Assert;

use Attribute;
use Temkaa\Validator\Constraint\ConstraintInterface;
use Temkaa\Validator\Constraint\Validator\NotBlankValidator;

/**
 * @api
 *
 * @template-implements ConstraintInterface<NotBlankValidator>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class NotBlank implements ConstraintInterface
{
    public function __construct(
        public string $message,
        public bool $allowNull = false,
    ) {
    }

    public function getHandler(): string
    {
        return NotBlankValidator::class;
    }
}
