<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint\Assert;

use Attribute;
use Temkaa\SimpleValidator\AbstractConstraintValidator;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\Validator\RegexValidator;

/**
 * @psalm-api
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Regex implements ConstraintInterface
{
    /**
     * @param non-empty-string $pattern
     * @psalm-api
     */
    public function __construct(
        public string $pattern,
        public string $message,
    ) {
    }

    public function getHandler(): AbstractConstraintValidator
    {
        return new RegexValidator();
    }
}
