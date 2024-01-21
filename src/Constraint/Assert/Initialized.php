<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint\Assert;

use Attribute;
use Temkaa\SimpleValidator\AbstractConstraintValidator;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;
use Temkaa\SimpleValidator\Constraint\Validator\InitializedValidator;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Initialized implements ConstraintInterface
{
    /**
     * @psalm-api
     */
    public function __construct(
        public string $message,
    ) {
    }

    public function getHandler(): AbstractConstraintValidator
    {
        return new InitializedValidator();
    }
}
