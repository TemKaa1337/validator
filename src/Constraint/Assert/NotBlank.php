<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint\Assert;

use Attribute;
use Temkaa\SimpleValidator\AbstractConstraintValidator;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\Validator\NotBlankValidator;

/**
 * @psalm-api
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class NotBlank implements ConstraintInterface
{
    /**
     * @psalm-api
     */
    public function __construct(
        public string $message,
        public bool $allowNull = false,
    ) {
    }

    public function getHandler(): AbstractConstraintValidator
    {
        return new NotBlankValidator();
    }
}
