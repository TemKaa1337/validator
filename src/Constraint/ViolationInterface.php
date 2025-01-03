<?php

declare(strict_types=1);

namespace Temkaa\Validator\Constraint;

/**
 * @api
 */
interface ViolationInterface
{
    public function getInvalidValue(): mixed;

    public function getMessage(): string;

    public function getPath(): string;
}
