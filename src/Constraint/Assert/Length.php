<?php

declare(strict_types=1);

namespace Temkaa\Validator\Constraint\Assert;

use Attribute;
use Temkaa\Validator\Constraint\ConstraintInterface;
use Temkaa\Validator\Constraint\Validator\LengthValidator;
use Temkaa\Validator\Exception\InvalidConstraintConfigurationException;

/**
 * @api
 *
 * @template-implements ConstraintInterface<LengthValidator>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Length implements ConstraintInterface
{
    public function __construct(
        public ?int $minLength = null,
        public ?int $maxLength = null,
        public ?string $minMessage = null,
        public ?string $maxMessage = null,
    ) {
        $this->validateGenericConfiguration();
        $this->validateMinConfiguration();
        $this->validateMaxConfiguration();
    }

    public function getHandler(): string
    {
        return LengthValidator::class;
    }

    private function validateGenericConfiguration(): void
    {
        if ($this->minLength === null && $this->maxLength === null) {
            throw new InvalidConstraintConfigurationException(
                'Length constraint must have one of "minLength" or "maxLength" argument set.',
            );
        }

        if ($this->minMessage === null && $this->maxMessage === null) {
            throw new InvalidConstraintConfigurationException(
                'Length constraint must have one of "minMessage" or "maxMessage" argument set.',
            );
        }

        if ($this->minLength !== null && $this->maxLength !== null && $this->maxLength < $this->minLength) {
            throw new InvalidConstraintConfigurationException(
                'Argument "maxLength" of Length constraint must be equal or greater than "minLength" value.',
            );
        }
    }

    private function validateMaxConfiguration(): void
    {
        if ($this->maxLength !== null && $this->maxLength < 0) {
            throw new InvalidConstraintConfigurationException(
                'Argument "maxLength" of Length constraint must be equal or greater than 0.',
            );
        }
    }

    private function validateMinConfiguration(): void
    {
        if (
            ($this->minLength !== null && $this->minMessage === null)
            || ($this->minLength === null && $this->minMessage !== null)
        ) {
            throw new InvalidConstraintConfigurationException(
                'Length constraint must have both "minLength" and "minMessage" arguments set.',
            );
        }

        if ($this->minLength !== null && $this->minLength < 0) {
            throw new InvalidConstraintConfigurationException(
                'Argument "minLength" of Length constraint must be equal or greater than 0.',
            );
        }
    }
}
