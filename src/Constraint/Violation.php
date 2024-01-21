<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint;

final readonly class Violation implements ViolationInterface
{
    public function __construct(
        private mixed $invalidValue,
        private string $message,
        private ?string $path,
    ) {
    }

    public function getInvalidValue(): mixed
    {
        return $this->invalidValue;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }
}
