<?php

declare(strict_types=1);

namespace Temkaa\Validator\Model;

/**
 * @api
 */
final readonly class ValidatedValue implements ValidatedValueInterface
{
    public function __construct(
        private mixed $value,
        private string $path,
        private bool $isInitialized,
    ) {
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function isInitialized(): bool
    {
        return $this->isInitialized;
    }
}
