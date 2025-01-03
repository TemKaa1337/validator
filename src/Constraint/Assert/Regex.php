<?php

declare(strict_types=1);

namespace Temkaa\Validator\Constraint\Assert;

use Attribute;
use Temkaa\Validator\Constraint\ConstraintInterface;
use Temkaa\Validator\Constraint\Validator\RegexValidator;

/**
 * @api
 *
 * @template-implements ConstraintInterface<RegexValidator>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Regex implements ConstraintInterface
{
    /**
     * @param non-empty-string $pattern
     */
    public function __construct(
        public string $pattern,
        public string $message,
    ) {
    }

    public function getHandler(): string
    {
        return RegexValidator::class;
    }
}
