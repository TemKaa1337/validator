<?php

declare(strict_types=1);

namespace Temkaa\Validator\Constraint\Assert;

use Attribute;
use Temkaa\Validator\Constraint\ConstraintInterface;
use Temkaa\Validator\Constraint\Validator\RangeValidator;
use Temkaa\Validator\Exception\InvalidConstraintConfigurationException;

/**
 * @api
 *
 * @template-implements ConstraintInterface<RangeValidator>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Range implements ConstraintInterface
{
    public function __construct(
        public float|int|null $min = null,
        public float|int|null $max = null,
        public ?string $minMessage = null,
        public ?string $maxMessage = null,
    ) {
        $this->validateGenericConfiguration();
        $this->validateMinConfiguration();
    }

    public function getHandler(): string
    {
        return RangeValidator::class;
    }

    private function validateGenericConfiguration(): void
    {
        if ($this->min === null && $this->max === null) {
            throw new InvalidConstraintConfigurationException(
                'Range constraint must have one of "min" or "max" argument set.',
            );
        }

        if ($this->minMessage === null && $this->maxMessage === null) {
            throw new InvalidConstraintConfigurationException(
                'Range constraint must have one of "minMessage" or "maxMessage" argument set.',
            );
        }

        if ($this->min !== null && $this->max !== null && $this->max < $this->min) {
            throw new InvalidConstraintConfigurationException(
                'Argument "max" of Range constraint must be equal or greater than "min" value.',
            );
        }
    }

    private function validateMinConfiguration(): void
    {
        if (
            ($this->min !== null && $this->minMessage === null)
            || ($this->min === null && $this->minMessage !== null)
        ) {
            throw new InvalidConstraintConfigurationException(
                'Range constraint must have both "min" and "minMessage" arguments set.',
            );
        }
    }
}
