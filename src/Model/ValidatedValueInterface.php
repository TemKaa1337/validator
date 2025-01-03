<?php

declare(strict_types=1);

namespace Temkaa\Validator\Model;

/**
 * @api
 */
interface ValidatedValueInterface
{
    public function getPath(): string;

    public function getValue(): mixed;

    public function isInitialized(): bool;
}
