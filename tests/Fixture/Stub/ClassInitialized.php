<?php

declare(strict_types=1);

namespace Tests\Fixture\Stub;

use Attribute;
use Temkaa\SimpleValidator\AbstractConstraintValidator;
use Temkaa\SimpleValidator\Constraint\ConstraintInterface;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class ClassInitialized implements ConstraintInterface
{
    public function __construct(
        public string $message,
    ) {
    }

    public function getHandler(): AbstractConstraintValidator
    {
        return new ClassInitializerValidator();
    }
}
