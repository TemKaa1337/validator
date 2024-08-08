<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint\Assert;

use Attribute;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\Validator\LengthValidator;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Length implements ConstraintInterface
{
    /**
     * @psalm-api
     */
    public function __construct(
        public ?int $minLength = null,
        public ?int $maxLength = null,
        public ?string $minMessage = null,
        public ?string $maxMessage = null,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getHandler(): string
    {
        return LengthValidator::class;
    }
}
