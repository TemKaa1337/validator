<?php

declare(strict_types=1);

namespace Temkaa\SimpleValidator\Constraint;

interface ViolationInterface
{
    public function getInvalidValue(): mixed;

    public function getMessage(): string;

    public function getPath(): ?string;
}
