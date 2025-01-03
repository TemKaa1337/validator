<?php

declare(strict_types=1);

namespace Tests\Helper\Stub;

use Attribute;
use Temkaa\Validator\Constraint\ConstraintInterface;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class ConstraintWithConfigurableHandler implements ConstraintInterface
{
    public function __construct(
        private string $handler,
    ) {
    }

    /**
     * @psalm-suppress LessSpecificReturnStatement, MoreSpecificReturnStatement
     */
    public function getHandler(): string
    {
        return $this->handler;
    }
}
